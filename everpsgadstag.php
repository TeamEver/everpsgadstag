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

Class EverPsGAdsTag extends Module
{
    private $_html = '';
    private $_postErrors = array();

    public $id_gtag;
    public $string;

    public function __construct()
    {
        $this->name = 'everpsgadstag';
        $this->tab = 'others';
        $this->version = '1.0.0';
        $this->author = 'Team Ever'; 

        $this->id_gtag = Configuration::get('ID_GTAG');

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Ever Ps Google Ads Tag');
        $this->description = $this->l('This module modifies your HTML code to include the tag required by Google Ads to work properly.');
        $this->confirmUninstall = $this->l('Are you sure you want to remove the Google Ads Tag?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);       

        if (!isset($this->id_gtag))
            $this->warning = $this->l('The "GTag" fields must be configured before using this module.');
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('displayHeader'))
            return false;
        return true;
    }

    public function uninstall()
    {
        if (!Configuration::deleteByName('ID_GTAG') || !parent::uninstall())
            return false;
        return true;
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            if (!Tools::getValue('ID_GTAG'))
                $this->_postErrors[] = $this->l('The "GTag" field is required.');
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit'))
        {
            Configuration::updateValue('ID_GTAG', Tools::getValue('ID_GTAG'));
        }

        $this->_html .= $this->displayConfirmation($this->l('GTag successfully updated'));
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
        $this->_html = '';

        if (Tools::isSubmit('btnSubmit'))
        {
            $this->_postValidation();
            if (!count($this->_postErrors))
                $this->_postProcess();
            else
                foreach ($this->_postErrors as $err)
                    $this->_html .= $this->displayError($err);
        }

        $this->_html .= $this->renderForm();

        return $this->_html;
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
                        'name' => 'ID_GTAG',
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
            'ID_GTAG' => Tools::getValue('ID_GTAG', Configuration::get('ID_GTAG')),
        );
    }
}
