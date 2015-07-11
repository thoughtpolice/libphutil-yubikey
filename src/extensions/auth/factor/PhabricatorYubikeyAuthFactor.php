<?php

final class PhabricatorYubikeyAuthFactor extends PhabricatorAuthFactor {

  public function getFactorKey() {
    return 'yubikey';
  }

  public function getFactorName() {
    return pht('Yubikey OTP');
  }

  public function getFactorDescription() {
    return pht(
      'Attach a Yubikey to your account, which has been synchronized '.
      'with the public YubiCloud validation server. When you need to '.
      'authenticate, you will use your Yubikey to enter the code to '.
      'complete authentication.');
  }

  public function processAddFactorForm(
    AphrontFormView $form,
    AphrontRequest $request,
    PhabricatorUser $user) {

    $code = $request->getStr('otpcode');

    $e_code = true;
    if ($request->getExists('otp')) {
      $userkey = self::getOTPUserSecret($code);
      $okay = self::verifyYubikeyOTPCode(
        new PhutilOpaqueEnvelope($userkey), $code);

      if ($okay) {
        $config = $this->newConfigForUser($user)
          ->setFactorName(pht('Yubikey OTP (using Public YubiCloud)'))
          ->setFactorSecret($userkey);

        return $config;
      } else {
        if (!strlen($code)) {
          $e_code = pht('Required');
        } else {
          $e_code = pht('Invalid');
        }
      }
    }

    $form->addHiddenInput('otp', true);

    if (!self::isYubicloudEnabled()) {
      $form->appendRemarkupInstructions(
        pht(
          'IMPORTANT: Your administrator has not yet configured the '.
          'necessary options to use Yubikey authenication. You should '.
          'annoy them until they relent.'));
    }

    $form->appendRemarkupInstructions(
      pht(
        'First, use the **Yubikey Personalization Tool** to add a '.
        'new **Yubico OTP** factor to your Yubikey (you can use '.
        'the **Quick** setup).'));

    $form->appendRemarkupInstructions(
      pht(
        'After configuring the Yubikey, be sure to **Upload the '.
        'key to Yubico**. This will open your web browser, and '.
        'allow you to publish your key to their server.'));

    $form->appendRemarkupInstructions(
      pht('NOTE: Key synchronization **can take up to 15 minutes**!'));

    $form->appendInstructions(
      pht('Afterwords, use your Yubikey to enter an OTP code below:'));

    $form->appendChild(
      id(new AphrontFormTextControl())
        ->setLabel(pht('OTP Code'))
        ->setName('otpcode')
        ->setValue($code)
        ->setError($e_code));
  }

  public function renderValidateFactorForm(
    PhabricatorAuthFactorConfig $config,
    AphrontFormView $form,
    PhabricatorUser $viewer,
    $validation_result) {

    if (!$validation_result) {
      $validation_result = array();
    }

    $form->appendChild(
      id(new AphrontFormTextControl())
        ->setName($this->getParameterName($config, 'otpcode'))
        ->setLabel(pht('OTP Code'))
        ->setCaption(pht('Factor Name: %s', $config->getFactorName()))
        ->setValue(idx($validation_result, 'value'))
        ->setError(idx($validation_result, 'error', true)));
  }

  public function processValidateFactorForm(
    PhabricatorAuthFactorConfig $config,
    PhabricatorUser $viewer,
    AphrontRequest $request) {

    $otp = $request->getStr($this->getParameterName($config, 'otpcode'));
    $userkey = new PhutilOpaqueEnvelope($config->getFactorSecret());

    if (self::verifyYubikeyOTPCode($userkey, $otp)) {
      return array(
        'error' => null,
        'value' => $otp,
        'valid' => true,
      );
    } else {
      return array(
        'error' => strlen($otp) ? pht('Invalid') : pht('Required'),
        'value' => $otp,
        'valid' => false,
      );
    }
  }

  /**
   * @phutil-external-symbol class Auth_Yubico
   */
  private static function verifyYubikeyOTPCode($userkey, $otp) {
    $root = dirname(phutil_get_library_root('libphutil-yubikey'));
    require_once $root.'/externals/php-yubico/Yubico.php';

    if (!self::isYubicloudEnabled()) {
      return false;
    }

    $client_id  = strval(PhabricatorEnv::getEnvConfig('yubicloud.client-id'));
    $secret_key = PhabricatorEnv::getEnvConfig('yubicloud.secret-key');

    $keyid_matches = $userkey->openEnvelope() === self::getOTPUserSecret($otp);
    $verified = id(new Auth_Yubico($client_id, $secret_key))
      ->verify($otp);

    return $keyid_matches && !id(new PEAR())->isError($verified);
  }

  private static function isYubicloudEnabled() {
    return
      PhabricatorEnv::getEnvConfig('yubicloud.client-id') &&
      PhabricatorEnv::getEnvConfig('yubicloud.secret-key');
  }

  private static function getOTPUserSecret($otp) {
    return substr($otp, 0, 12);
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
