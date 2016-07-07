<?php
/**
 * Module "Fasardi XML" for Prestashop 1.6.0.9
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Main module class.
 *
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @since 1.0.0
 */
class Fasardixml extends Module
{
    /**
     * Name of preference with exchange rate between PLN and CZK.
     * @var string
     */
    const PREF_RATE_PLN_CZK = 'PREF_RATE_PLN_CZK';

    /**
     * Name of preference with feed url.
     * @var string
     */
    const PREF_FEED_URL = 'PREF_FEED_URL';

    /**
     * Name of preference for using of multi-language fields.
     * @var string
     */
    const PREF_MULTI_LANG = 'PREF_MULTI_LANG';

    /**
     * Default value of preference for exchange rate between PLN and CZK.
     * @var string
     */
    const DEFAULT_RATE_PLN_CZK = '7.0';

    /**
     * Default value of preference for feed url.
     * @var string
     */
    const DEFAULT_FEED_URL = 'http://www.fasardi.com/media/amfeed/feeds/fasardiofficial.xml';

    /**
     * Default value of preference for using of multi-language fields.
     * @var boolean
     */
    const DEFAULT_MULTI_LANG = false;

    /**
     * @since 1.0.0
     * @see ModuleCore::__construct()
     */
    public function __construct()
    {
        $this->name = 'fasardixml';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Ondřej Doněk';
        $this->need_instance = 1;
        //$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->_directory = dirname(__FILE__);

        parent::__construct();

        $this->displayName = $this->l('Fasardi XML');
        $this->description = $this->l('Module for importing XML feed with products from e-shop Fasardi.com.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
    }

    /**
     * @since 1.0.0
     * @return boolean
     * @see ModuleCore::install()
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        Configuration::updateValue(self::PREF_RATE_PLN_CZK, self::DEFAULT_RATE_PLN_CZK);
        Configuration::updateValue(self::PREF_FEED_URL, self::DEFAULT_FEED_URL);
        Configuration::updateValue(self::PREF_MULTI_LANG, self::DEFAULT_MULTI_LANG);

        //$this->registerHook('header');

        return true;
    }

    /**
     * @since 1.0.0
     * @return boolean
     * @see ModuleCore::uninstall()
     */
    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        Configuration::deleteByName(self::PREF_RATE_PLN_CZK);
        Configuration::deleteByName(self::PREF_FEED_URL);
        Configuration::deleteByName(self::PREF_MULTI_LANG);

        return true;
    }

    /**
     * @since 1.0.0
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit'.$this->name)) {
            $pref_ex_rate = Tools::getValue(self::PREF_RATE_PLN_CZK);
            Configuration::updateValue(self::PREF_RATE_PLN_CZK, $pref_ex_rate);
            
            $pref_feed_url = Tools::getValue(self::PREF_FEED_URL);
            if (filter_var($pref_feed_url, FILTER_VALIDATE_URL)) {
                Configuration::updateValue(self::PREF_FEED_URL, $pref_feed_url);
            } else {
                $output .= $this->displayError($this->l('Invalid value for feed URL.'));
            }

            $pref_multi_lang = (bool)Tools::getValue(self::PREF_MULTI_LANG);
            Configuration::updateValue(self::PREF_MULTI_LANG, $pref_multi_lang);

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output.$this->displayForm();
    }

    /**
     * @since 1.0.0
     */
    public function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cog',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Exchange rate PLN to CZK'),
                    'name' => self::PREF_RATE_PLN_CZK,
                    'size' => 5,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Fasardi XML feed URL'),
                    'name' => self::PREF_FEED_URL,
                    'size' => 35,
                    'required' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use full multi-language fields'),
                    'name' => self::PREF_MULTI_LANG,
                    'desc' => $this->l('Fill all available languages by the value from import or just the default language.'),
                    'values' => $this->getFormSwitchValues(strtolower(self::PREF_MULTI_LANG))
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ),
        );

        $helper = new HelperForm();
         
        // Main properties
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submit'.$this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Toolbar
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->toolbar_btn = array(
            'save' => array(
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ),
            'back' => array(
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        // Current values
        $helper->fields_value[self::PREF_RATE_PLN_CZK] = Configuration::get(self::PREF_RATE_PLN_CZK);
        $helper->fields_value[self::PREF_FEED_URL] = Configuration::get(self::PREF_FEED_URL);
        $helper->fields_value[self::PREF_MULTI_LANG] = Configuration::get(self::PREF_MULTI_LANG);
         
        return $helper->generateForm($fields_form);
    }

    /**
     * @param string $id
     * @return array
     */
    private function getFormSwitchValues($id) {
        return array(
            array(
                'id' => $id.'_on',
                'value' => 1,
                'label' => $this->l('Yes')
            ),
            array(
                'id' => $id.'_off',
                'value' => 0,
                'label' => $this->l('No')
            )
        );
    }
}