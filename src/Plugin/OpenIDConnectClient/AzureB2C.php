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

  // String Used in help texts.
  const B2C_SAMPLE_URL = "https://login.microsoft.com/<tenant-name>.onmicrosoft.com/<policy-name>/oauth2/v2.0/authorize";

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
    // @todo add sample default endpoint & link to B2C setup Page.
    $form['azure_b2c_authorization_endpoint'] = [
      '#title' => $this->t('Authorization endpoint'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#default_value' => $this->configuration['azure_b2c_authorization_endpoint'] ?? '',
    ];
    // @todo . Explain why we say if the token endpoint needs to be overridden.
    $form['azure_b2c_token_endpoint_override'] = [
      '#title' => $this->t('Have a custom Token Endpoint ?'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['azure_b2c_token_endpoint_override'] ?? '',
      '#description' => $this->t('Enabled if you want to provide an overridden Token Endpoint.'),
    ];
    $form['azure_b2c_token_endpoint'] = [
      '#title' => $this->t('Token endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['azure_b2c_token_endpoint'] ?? '',
      '#states' => [
        'visible' => [
          ':input[name="clients[azure_b2c][settings][azure_b2c_token_endpoint_override]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    // @todo . Explain why UserInfo End Point is not mandatory for B2C.
    $form['azure_b2c_userinfo_endpoint_exist'] = [
      '#title' => $this->t('Have a custom UserInfo Endpoint ?'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['azure_b2c_token_endpoint_override'] ?? 0,
      '#description' => $this->t('Enabled if you have a UserInfo Endpoint to be configured.'),
    ];
    $form['azure_b2c_userinfo_endpoint'] = [
      '#title' => $this->t('UserInfo endpoint'),
      '#type' => 'textfield',
      '#default_value' => $this->configuration['azure_b2c_userinfo_endpoint'] ?? '',
      '#states' => [
        'visible' => [
          ':input[name="clients[azure_b2c][settings][azure_b2c_userinfo_endpoint_exist]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['azure_b2c_single_sign_out'] = [
      '#title' => $this->t('Enable Drupal-invoked single sign-out'),
      '#type' => 'checkbox',
      '#default_value' => $this->configuration['azure_b2c_sign_out'] ?? 0,
      '#description' => $this->t("Enabled if Drupal should sign out the user from Azure AD B2C when initiated from a logout is click."),
    ];

    // Offer to Pass language parameter to AD B2C, on multilingual site.
    if (\Drupal::languageManager()->isMultilingual()) {
      $form['azure_b2c_site_language_exchange'] = [
        '#title' => $this->t('eXchange User Language with Azure AD B2C ?'),
        '#type' => 'checkbox',
        '#default_value' => $this->configuration['azure_b2c_sign_out'] ?? 0,
        '#description' => $this->t("Enabled if Drupal should pass users site language as a parameter to b2c."),
      ];
      $form['azure_b2c_site_language_parameter'] = [
        '#title' => $this->t('Define the Variable name to be used for passing language.'),
        '#type' => 'textfield',
        '#default_value' => $this->configuration['azure_b2c_site_language_parameter'] ?? 'language',
        '#description' => $this->t('By default <b>language</b> would be the Variable passed to B2C, unless overridden above. Ex. URL <a href=":url">:url</a>.', [':url' => self::B2C_SAMPLE_URL]),
        '#states' => [
          'visible' => [
            ':input[name="clients[azure_b2c][settings][azure_b2c_site_language_exchange]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

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
    if ($configuration['azure_b2c_token_endpoint_override'] && empty($configuration['azure_b2c_token_endpoint'])) {
      $form_state->setErrorByName('azure_b2c_token_endpoint', $this->t('The Token endpoint is missing for @provider.', $provider));
    }
    if ($configuration['azure_b2c_userinfo_endpoint_exist'] && empty($configuration['azure_b2c_userinfo_endpoint'])) {
      $form_state->setErrorByName('azure_b2c_userinfo_endpoint', $this->t('The UserInfo endpoint is missing for @provider.', $provider));
    }
    if ($configuration['azure_b2c_site_language_exchange'] && empty($configuration['azure_b2c_site_language_parameter'])) {
      $form_state->setErrorByName('azure_b2c_site_language_parameter', $this->t('The UserInfo endpoint is missing for @provider.', $provider));
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
    $endpoints = [];
    $endpoints['authorization'] = $this->generateTokenEndpoints();
    $endpoints['token'] = $this->generateTokenEndpoints();
    // Include UserInfo EndPoint if an Override is provided.
    if ($this->configuration['azure_b2c_userinfo_endpoint_exist'] && !empty($this->configuration['azure_b2c_userinfo_endpoint'])) {
      $endpoints['userinfo'] = $this->configuration['azure_b2c_userinfo_endpoint'];
    }
    return $endpoints;
  }

  /**
   * Generates & Returns token endpoint.
   *
   * @param string $endpoint_type
   *   endpoint identifier & defaults to token.
   *
   * @return string
   *   Endpoint URL .
   */
  public function generateTokenEndpoints($endpoint_type) {
    if ($endpoint_type == 'token' && $this->configuration['azure_b2c_token_endpoint_override']) {
      return $this->configuration['azure_b2c_token_endpoint_override'];
    }
    return str_ireplace("/authorize", "/token", $this->configuration['azure_b2c_authorization_endpoint']);
  }

}
