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
     * Names of single preferences of module settings.
     */
    const PREF_RATE_PLN_CZK = 'PREF_RATE_PLN_CZK';
    const PREF_FEED_URL = 'PREF_FEED_URL';
    const PREF_MULTI_LANG = 'PREF_MULTI_LANG';
    const PREF_DEFAULT_CAT = 'PREF_DEFAULT_CAT';
    const PREF_DEFAULT_ONSTOCK = 'PREF_DEFAULT_ONSTOCK';
    const PREF_USE_OLDPRICE = 'PREF_USE_OLDPRICE';
    const PREF_ADDITIONAL_COST = 'PREF_ADDITIONAL_COST';
    const PREF_USE_HIGHPRICE = 'PREF_USE_HIGHPRICE';
    const PREF_USE_COMBINATIONS = 'PREF_USE_COMBINATIONS';

    /**
     * Default values for module settings.
     */
    const DEFAULT_RATE_PLN_CZK = '7.0';
    const DEFAULT_FEED_URL = 'http://www.fasardi.com/media/amfeed/feeds/fasardiofficial.xml';
    const DEFAULT_MULTI_LANG = false;
    const DEFAULT_DEFAULT_CAT = 12;
    const DEFAULT_DEFAULT_ONSTOCK = 20;
    const DEFAULT_USE_OLDPRICE = true;
    const DEFAULT_ADDITIONAL_COST = 350;
    const DEFAULT_USE_HIGHPRICE = true;
    const DEFAULT_USE_COMBINATIONS = true;

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
     * @todo Check if category with id defined in `DEFAULT_DEFAULT_CAT` exists, if not set `NULL`.
     */
    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        Configuration::updateValue(self::PREF_RATE_PLN_CZK, self::DEFAULT_RATE_PLN_CZK);
        Configuration::updateValue(self::PREF_FEED_URL, self::DEFAULT_FEED_URL);
        Configuration::updateValue(self::PREF_MULTI_LANG, self::DEFAULT_MULTI_LANG);
        Configuration::updateValue(self::PREF_DEFAULT_CAT, self::DEFAULT_DEFAULT_CAT);
        Configuration::updateValue(self::PREF_DEFAULT_ONSTOCK, self::DEFAULT_DEFAULT_ONSTOCK);
        Configuration::updateValue(self::PREF_USE_OLDPRICE, self::DEFAULT_USE_OLDPRICE);
        Configuration::updateValue(self::PREF_ADDITIONAL_COST, self::DEFAULT_ADDITIONAL_COST);
        configuration::updateValue(self::PREF_USE_HIGHPRICE, self::DEFAULT_USE_HIGHPRICE);
        Configuration::updateValue(self::PREF_USE_COMBINATIONS, self::DEFAULT_USE_COMBINATIONS);

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
        Configuration::deleteByName(self::PREF_DEFAULT_CAT);
        Configuration::deleteByName(self::PREF_DEFAULT_ONSTOCK);
        Configuration::deleteByName(self::PREF_USE_OLDPRICE);
        Configuration::deleteByName(self::PREF_ADDITIONAL_COST);
        configuration::deleteByName(self::PREF_USE_HIGHPRICE);
        configuration::deleteByName(self::PREF_USE_COMBINATIONS);

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

            $pref_default_cat = (int)Tools::getValue('categoryBox'/*self::PREF_DEFAULT_CAT*/);
            Configuration::updateValue(self::PREF_DEFAULT_CAT, $pref_default_cat);

            $pref_default_stock = (int)Tools::getValue(self::PREF_DEFAULT_ONSTOCK);
            Configuration::updateValue(self::PREF_DEFAULT_ONSTOCK, $pref_default_stock);

            $pref_additional_cost = (float)Tools::getValue(self::PREF_ADDITIONAL_COST);
            Configuration::updateValue(self::PREF_ADDITIONAL_COST, $pref_additional_cost);

            $pref_old_stock = (bool)Tools::getValue(self::PREF_USE_OLDPRICE);
            Configuration::updateValue(self::PREF_USE_OLDPRICE, $pref_old_stock);

            $pref_use_highprice = (bool)Tools::getValue(self::PREF_USE_HIGHPRICE);
            Configuration::updateValue(self::PREF_USE_HIGHPRICE, $pref_use_highprice);

            $pref_use_combinations = (bool)Tools::getValue(self::PREF_USE_COMBINATIONS);
            Configuration::updateValue(self::PREF_USE_COMBINATIONS, $pref_use_combinations);

            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output.$this->displayForm();
    }

    /**
     * @since 1.0.0
     */
    public function displayForm()
    {
        /**
         * @var integer $default_lang
         */
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        /**
         * @var HelperForm $helper
         */
        $helper = new HelperForm();

        /**
         * @var HelperTreeCategories $cat_tree_helper
         */
        $cat_tree_helper = new HelperTreeCategories(self::PREF_DEFAULT_CAT);
        // Set root category
        $cat_tree_helper->setRootCategory((Shop::getContext() == Shop::CONTEXT_SHOP ? Category::getRootCategory()->id_category : 0));
        /**
         * @var string $cat_tree
         */
        $cat_tree = $cat_tree_helper->render();

        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cog',
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Exchange rate PLN to CZK'),
                    'desc' => $this->l('Set used exchange rate between PLN and CZK.'),
                    'name' => self::PREF_RATE_PLN_CZK,
                    'size' => 5,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Fasardi XML feed URL'),
                    'desc' => $this->l('Enter URL address of the XML feed provided by Fasardi.com.'),
                    'name' => self::PREF_FEED_URL,
                    'size' => 35,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Default on stock'),
                    'desc' => $this->l('Default value for on stock property for imported products.'),
                    'name' => self::PREF_DEFAULT_ONSTOCK,
                    'size' => 5,
                    'required' => true,
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Additional shipping cost'),
                    'desc' => $this->l('Additional shipping cost will be added to final price of imported products.'),
                    'name' => self::PREF_ADDITIONAL_COST,
                    'size' => 5,
                    'required' => true,
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use old price'),
                    'desc' => $this->l('Use old price as a price for imported products.'),
                    'name' => self::PREF_USE_OLDPRICE,
                    'values' => $this->getFormSwitchValues(strtolower(self::PREF_USE_OLDPRICE)),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use highest price'),
                    'desc' => $this->l('Use highest price (old or new) as a price for imported products (this setting has higher pririty than setting about old price).'),
                    'name' => self::PREF_USE_HIGHPRICE,
                    'values' => $this->getFormSwitchValues(strtolower(self::PREF_USE_HIGHPRICE)),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Create combinations'),
                    'desc' => $this->l('Create combinations from imported products with same name and description which differ just in color.'),
                    'name' => self::PREF_USE_COMBINATIONS,
                    'values' => $this->getFormSwitchValues(strtolower(self::PREF_USE_COMBINATIONS)),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Use full multi-language fields'),
                    'name' => self::PREF_MULTI_LANG,
                    'desc' => $this->l('Fill all available languages by the value from import or just the default language.'),
                    'values' => $this->getFormSwitchValues(strtolower(self::PREF_MULTI_LANG)),
                ),
                array(
                    'type'  => 'categories_select',
                    'label' => $this->l('Default category'),
                    'desc'    => $this->l('Default category for imported products.'),  
                    'name'  => Fasardixml::PREF_DEFAULT_CAT,
                    'category_tree'  => $cat_tree,
                    'required' => true,
                ),
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ),
        );
         
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
        $helper->fields_value[self::PREF_MULTI_LANG] = ((bool)Configuration::get(self::PREF_MULTI_LANG) == true ? '1' : '0');
        $helper->fields_value[self::PREF_DEFAULT_CAT] = Configuration::get(self::DEFAULT_DEFAULT_CAT);
        $helper->fields_value['categoryBox'] = Configuration::get(self::DEFAULT_DEFAULT_CAT);
        $helper->fields_value[self::PREF_DEFAULT_ONSTOCK] = Configuration::get(self::PREF_DEFAULT_ONSTOCK);
        $helper->fields_value[self::PREF_ADDITIONAL_COST] = Configuration::get(self::PREF_ADDITIONAL_COST);
        $helper->fields_value[self::PREF_USE_OLDPRICE] = ((bool)Configuration::get(self::PREF_USE_OLDPRICE) == true ? '1' : '0');
        $helper->fields_value[self::PREF_USE_HIGHPRICE] = ((bool)Configuration::get(self::PREF_USE_HIGHPRICE) == true ? '1' : '0');
        $helper->fields_value[self::PREF_USE_COMBINATIONS] = ((bool)Configuration::get(self::PREF_USE_COMBINATIONS) == true ? '1' : '0');

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