<?php

/**
 * Adds a section on the 'Config' application for configuring
 * Yubikey-related options.
 */
final class PhabricatorYubikeyConfigOptions
  extends PhabricatorApplicationConfigOptions {

  public function getName() {
    return pht('Yubikey OTP Support');
  }

  public function getDescription() {
    return pht('Configure Yubikey OTP integration.');
  }

  public function getFontIcon() {
    return 'fa-key';
  }

  public function getGroup() {
    return 'core';
  }

  public function getOptions() {
    return array(
      /* -- Yubikey server options. -- */
      $this->newOption('yubicloud.client-id', 'int', null)
        ->setLocked(true)
        ->setDescription(pht('YubiCloud API client ID.')),
      $this->newOption('yubicloud.secret-key', 'string', null)
        ->setLocked(true)
        ->setHidden(true)
        ->setDescription(pht('YubiCloud secret API key.')),
    );
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
