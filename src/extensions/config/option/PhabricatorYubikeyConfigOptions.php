<?php

/**
 * Adds a section on the 'Config' application for configuring
 * Yubikey-related options.
 */
final class PhabricatorYubikeyConfigOptions
  extends PhabricatorApplicationConfigOptions {

  public function getName() {
    return pht('Yubikey support');
  }

  public function getDescription() {
    return pht('Configure Yubikey integration.');
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
        ->setDescription(pht('YubiCloud authentication Client ID.')),
      $this->newOption('yubicloud.secret-key', 'string', null)
        ->setLocked(true)
        ->setHidden(true)
        ->setDescription(pht('API key for YubiCloud authentication.')),
    );
  }
}

// Local Variables:
// fill-column: 80
// indent-tabs-mode: nil
// c-basic-offset: 2
// buffer-file-coding-system: utf-8-unix
// End:
