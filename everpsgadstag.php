<?php
/**
 * Prestashop module : everpsgadstag
 *
 * @author Team Ever <contact@team-ever.com>
 * @copyright  Team Ever
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EverPsGAdsTag extends Module
{
    private $html = '';
    private $postErrors = array();

    public function __construct()
    {
        $this->name = 'everpsgadstag';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Team Ever';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ever Ps Google Ads Tag');
        $this->description = $this->l('This module modifies your HTML code to include the tag required by Google Ads to work properly.');
        $this->confirmUninstall = $this->l('Are you sure you want to remove the Google Ads Tag?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('displayHeader')) {
            return false;
        }
        
        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('EVERPSGADSTAG_ID_GTAG') || !parent::uninstall()) {
            return false;
        }

        return true;
    }

    private function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('EVERPSGADSTAG_ID_GTAG')) {
                $this->postErrors[] = $this->l('The "GTag" field is required.');
            }
        }
    }

    private function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('EVERPSGADSTAG_ID_GTAG', Tools::getValue('EVERPSGADSTAG_ID_GTAG'));
        }

        $this->html .= $this->displayConfirmation($this->l('GTag successfully updated'));
    }

    public function hookDisplayHeader()
    {
        return "
            <!-- Global site tag (gtag.js) - Google AdWords: $this->id_gtag -->
            <script async src=\"https://www.googletagmanager.com/gtag/js?idi=$this->id_gtag\"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());

                gtag('config', '$this->id_gtag');
            </script>
        ";
    }

    public function getContent()
    {
        $this->html = '';

        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();

            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError($err);
                }
            }
        }

        $this->html .= $this->renderForm();

        return $this->html;
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Google Ads Tag Configuration'),
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('GTag (AW-XXXXXXXXXX) : AW-'),
                        'name' => 'EVERPSGADSTAG_ID_GTAG',
                        'desc' => 'Paste the 10 numbers of GTag here.',
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'EVERPSGADSTAG_ID_GTAG' => Tools::getValue('EVERPSGADSTAG_ID_GTAG', Configuration::get('EVERPSGADSTAG_ID_GTAG')),
        );
    }
}
