<?php

namespace Drupal\openid_connect_azure_ad_b2c\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for OpenID Connect Azure AD B2C routes.
 */
class OpenidConnectAzureAdB2cController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    return $build;
  }

}
