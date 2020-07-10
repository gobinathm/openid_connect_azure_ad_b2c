<?php

namespace Drupal\openid_connect_azure_ad_b2c\Plugin\OpenIDConnectClient;

use Drupal\Core\Form\FormStateInterface;
use Drupal\openid_connect\Plugin\OpenIDConnectClientBase;

/**
 * OpenID Connect client for Azure AD B2C.
 *
 * Used to login to Drupal sites using Azure AD B2C as authentication provider.
 *
 * @OpenIDConnectClient(
 *   id = "azure_b2c",
 *   label = @Translation("Azure AD B2C")
 * )
 */
class AzureB2C extends OpenIDConnectClientBase {

  /**
   * Overrides OpenIDConnectClientBase::settingsForm().
   *
   * @param array $form
   *   Azure AD B2C form array containing form elements.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   Submitted form values.
   *
   * @return array
   *   Renderable form array with form elements.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    return $form;
  }

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   *
   * @return array
   *   Endpoint details with authorization endpoints, user access token and
   *   userinfo object.
   */
  public function getEndpoints() {
    return NULL;
  }

}
