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
    $form['azure_b2c_authorization_endpoint'] = [
      '#title' => $this->t('Authorization endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['azure_b2c_authorization_endpoint'],
      '#required' => TRUE,
    ];
    $form['azure_b2c_token_endpoint'] = [
      '#title' => $this->t('Token endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['azure_b2c_token_endpoint'],
    ];
    $form['azure_b2c_userinfo_endpoint'] = [
      '#title' => $this->t('UserInfo endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['azure_b2c_userinfo_endpoint'],
    ];

    return $form;
  }

  /**
   * Overrides OpenIDConnectClientBase::validateConfigurationForm().
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $provider = [
      '@provider' => $this->getPluginDefinition()['label'],
    ];
    $configuration = $form_state->getValues();

    // Valid none of client config is empty.
    if (empty($configuration['client_id'])) {
      $form_state->setErrorByName('client_id', $this->t('The Client ID is missing for @provider.', $provider));
    }
    if (empty($configuration['client_secret'])) {
      $form_state->setErrorByName('client_secret', $this->t('The client Secret is missing for @provider.', $provider));
    }
    if (empty($configuration['azure_b2c_authorization_endpoint'])) {
      $form_state->setErrorByName('azure_b2c_authorization_endpoint', $this->t('The Authorization endpoint is missing for @provider.', $provider));
    }
    if (empty($configuration['azure_b2c_token_endpoint'])) {
      $form_state->setErrorByName('azure_b2c_token_endpoint', $this->t('The Token endpoint is missing for @provider.', $provider));
    }
    if (empty($configuration['azure_b2c_userinfo_endpoint'])) {
      $form_state->setErrorByName('azure_b2c_userinfo_endpoint', $this->t('The UserInfo endpoint is missing for @provider.', $provider));
    }
  }

  /**
   * Overrides OpenIDConnectClientBase::getEndpoints().
   *
   * @return array
   *   Endpoint details with authorization endpoints, user access token and
   *   userinfo object.
   */
  public function getEndpoints() {
    return [
      'authorization' => $this->configuration['azure_b2c_authorization_endpoint'],
      'token' => $this->configuration['azure_b2c_token_endpoint'],
      'userinfo' => $this->configuration['azure_b2c_userinfo_endpoint'],
    ];
  }

}
