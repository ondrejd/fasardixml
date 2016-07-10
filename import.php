<?php
/**
 * Script for importing Fasardi XML feed.
 *
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @package Fasardixml
 * @version 1.0.0
 */

/**
 * Include PrestaShop configuration file.
 */
include_once dirname(dirname(dirname(__FILE__))).'/config/config.inc.php';

/**
 * Include main module's file (because of class constants).
 */
include_once dirname(__FILE__).'/fasardixml.php';
error_reporting(E_ALL|E_NOTICES);
/**
 * Class that realizes import self.
 *
 * @author Ondřej Doněk <ondrejd@gmail.com>
 * @package Fasardixml
 * @since 1.0.0
 */
class FasardiXmlImport
{
	/**
	 * Holds exchange rate between PLN and CZK.
	 * @since 1.0.0
	 * @var float
	 */
	protected $exchange_rate;

	/**
	 * Holds URL of Fasardi XML feed.
	 * @since 1.0.0
	 * @var string $feed_url
	 */
	protected $feed_url;

	/**
	 * Holds path of `docs\xml_import` folder.
	 * @since 1.0.0
	 * @var string $docs_path 
	 */
	protected $docs_path;

	/**
	 * ID of default language.
	 * @var integer $default_lang_id
	 */
	protected $default_lang_id;

	/**
	 * ID of the default category.
	 * @var integer $default_category_id
	 */
	protected $default_category_id;

	/**
	 * Create multi-language fileds filled for all used languages or only for the default one?
	 * @var boolean $create_multilang_fields
	 */
	protected $create_multilang_fields;

	/**
	 * Value for onstock property for imported products.
	 * @var int $default_onstock
	 */
	protected $default_onstock;

	/**
	 * TRUE if old price should be used instead of new one for imported products.
	 * @var boolean $use_oldprice
	 */
	protected $use_oldprice;

	/**
	 * Preference with additional shipping cost which should be added to price of imported products.
	 * @var float $additional_cost
	 */
	protected $additional_cost;

	/**
	 * TRUE if the highest price should be used.
	 * @var boolean $use_highprice
	 */
	protected $use_highprice;

	/**
	 * TRUE if create combinations from same products that differ just by color.
	 * @var boolean $use_combinations
	 */
	protected $use_combinations;

	/**
	 * Holds name of file in which is downloaded feed storred.
	 * This property is filled in {@see FasardiXmlImport::download_feed}.
	 * @since 1.0.0
	 * @var string $feed_filename
	 */
	protected $feed_filename;

	/**
	 * Contents of the XML feed.
	 * This is filled in {@see FasardiXmlImport::download_feed} or {@see FasardiXmlImport::load_feed_from_url}
	 * @var DOMDocument $feed
	 */
	protected $feed;

	/**
	 * Array with products parsed from feed.
	 * This is produced by {@see FasardiXmlImport::parse_products}.
	 * @var array $products
	 */
	protected $products = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->exchange_rate = floatval(Configuration::get(Fasardixml::PREF_RATE_PLN_CZK, Fasardixml::DEFAULT_RATE_PLN_CZK));
		$this->feed_url = Configuration::get(Fasardixml::PREF_FEED_URL, Fasardixml::DEFAULT_FEED_URL);
		$this->docs_path = _PS_CORE_DIR_.'/docs/xml_import';
		$this->default_lang_id = (int)Configuration::get('PS_LANG_DEFAULT');
		$this->default_category_id = (int)Configuration::get(Fasardixml::PREF_DEFAULT_CAT, Fasardixml::DEFAULT_DEFAULT_CAT);
		$this->create_multilang_fields = (bool)Configuration::get(Fasardixml::PREF_MULTI_LANG, Fasardixml::DEFAULT_MULTI_LANG);
		$this->default_onstock = (int)Configuration::get(Fasardixml::PREF_DEFAULT_ONSTOCK, Fasardixml::DEFAULT_DEFAULT_ONSTOCK);
		$this->use_oldprice = (bool)Configuration::get(Fasardixml::PREF_USE_OLDPRICE, Fasardixml::DEFAULT_USE_OLDPRICE);
		$this->additional_cost = (float)Configuration::get(Fasardixml::PREF_ADDITIONAL_COST, Fasardixml::DEFAULT_ADDITIONAL_COST);
		$this->use_highprice = (bool)Configuration::get(Fasardixml::PREF_USE_HIGHPRICE, Fasardixml::DEFAULT_USE_HIGHPRICE);
		$this->use_combinations = (bool)Configuration::get(Fasardixml::PREF_USE_COMBINATIONS, Fasardixml::DEFAULT_USE_COMBINATIONS);

		// TODO Check database if there is pending job and if yes load it.
		// TODO If no pending job was found create new job.
		// TODO Start the job

		/**
		 * @todo The import is splitted to several jobs (downloading, parsing, creating)
		 *
		 * Jobs:
		 * 1) "downloading" - download xml feed to the file in `docs/xml_import`
		 * 2) "parsing" - parse XML to PHP array and serialize it to the file in `docs/xml_import`
		 * 3) "creating" - create products (categories and images) from the serialized PHP array
		 * 
		 * @todo Each job can be stopped and restored (based on database record).
		 */
	}

	/**
	 * Creates multi-language field.
	 * 
	 * @internal Use {@see create_language_field} instead.
	 *
	 * @since 1.0.0
	 * 
	 * @param string $field
	 * @return array
	 */
	protected function create_multi_language_field($field) {
		$languages = Language::getLanguages(false);
		$res = array();

		foreach ($languages as $lang) {
			$res[$lang['id_lang']] = $field;
		}

		return $res;
	}

	/**
	 * Creates single-language field (with the default language).
	 * 
	 * @internal Use {@see create_language_field} instead.
	 *
	 * @since 1.0.0
	 * 
	 * @global integer $default_lang_id
	 * @param string $field
	 * @return array
	 */
	protected function create_single_language_field($field) {
		global $default_lang_id;

		return array($default_lang_id => $field);
	}

	/**
	 * Creates single/multi-language field.
	 *
	 * Descision if single- or multi- language field should be used is 
	 * based on value of global `$create_multilang_fields` variable.
	 *
	 * @since 1.0.0
	 * @global boolean $create_multilang_fields
	 * @param string $field
	 * @return array
	 */
	protected function create_language_field($field) {
		global $create_multilang_fields;

		if ($create_multilang_fields === true) {
			return create_multi_language_field($field);
		}

		return create_single_language_field($field);
	}

	/**
	 * Parse string with names of categories.
	 *
	 * Missing categories will be created.
	 *
	 * @since 1.0.0
	 * @param string $categories Comma-separated list of names of categories.
	 * @param integer $default_category ID of the default category.
	 * @return array IDs of categories. 
	 */
	protected function import_categories($categories, $default_category) {
		$ret = array();
		$ret[] = $default_category;
		$_categories = array_unique(split(',', $categories));

		foreach ($_categories as $_category) {
			$category = new Category();
			$category->name = create_single_language_field($_category);
			$category->link_rewrite = create_single_language_field(Tools::link_rewrite($_category));
			//$category->description = create_single_language_field('');
			$category->active = 1;
			$category->id_parent = (int) $default_category_id;
			$category->add();

			if (isset($category->id)) {
				if (!empty($category->id)) {
					$ret[] = $category->id;
				}
			}
		}

		return $ret;
	}

	/**
	 * Saves (import) images.
	 * @since 1.0.0
	 * @param integer $product_id
	 * @param SimpleXMLElement $imgs
	 * @return array Returns IDs of new images.
	 * 
	 * @todo Import images!
	 * @todo Returns array such this `array('default' => 12, 'categories' => array(12,24,34,35))`!
	 */
	function import_images($product_id, $imgs) {
		foreach ($imgs->img as $img_url) {
			if (empty($img_url)) {
				continue;
			}

			//$img_url = $img;
			//echo "$img_url<br/>";
			$image = new Image();
			$image->id_product = $product_id;
		}
	}

	/**
	 * Execute import.
	 * @since 1.0.0
	 */
	public function execute() {
		$this->download_feed();
		$this->parse_products();
	}

	/**
	 * Download and save XML feed.
	 * 
	 * @since 1.0.0
	 * @throws Exception whenever error occured during downloading or saving.
	 */
	public function download_feed() {
		$this->feed_filename = $this->docs_path.'/'.date('Ymdhis').'.xml';
		$context = stream_context_create(array('http' => array('timeout' => 1)));
		$feed_str = file_get_contents($this->feed_url, 0, $context);

		if ($feed_str === false || empty($feed_str)) {
			throw new Exception('Error occured while downloading the Fasardi feed!');
		}

		$res = file_put_contents($this->feed_filename, $feed_str);

		if ($res === false) {
			throw new Exception('Error occured while saving imported XML file!');
		}

		$this->feed = new DOMDocument();
		$this->feed->load($this->feed_filename);
	}

	/**
	 * Parse products from feeds.
	 *
	 * This should ends with array full of products that are ready to be saved in DB.
	 *
	 * @since 1.0.0
	 */
	public function parse_products() {
		$this->products = array();
		$offers = $this->feed->getElementsByTagName('offer');


		foreach ($offers as $offer) {
echo '<pre>';var_dump($offer);exit();


			// Basic properties
			$product = array(
				'id'       => $offer->id,
				'name'     => $offer->name,
				'url'      => $offer->url,
				'desc'     => $offer->desc,
				'cat'      => $offer->cat,
				'price'    => (float)$offer->price,
				'oldprice' => (float)$offer->oldprice,
				'attrs'    => array(),
				'imgs'     => array(),
				'sizes'    => array(),
				'promoted' => (bool)$offer->isPromoted,
			);

			// Price
			if ($this->use_highprice === true) {
				if ($product['oldprice'] >= $product['price']) {
					$product['price'] = $product['oldprice'];
				}
			} elseif ($this->use_oldprice === true && $product['oldprice'] > 0) {
				$product['price'] = $product['oldprice'];
			}
			unset($product['oldprice']);

			// Attributes
			echo '<pre>';
			//var_dump($offer->attrs);
			foreach ($offer->attrs as $attr) {
				var_dump($attr);
				echo $offer->attrs[0]->__toString();
			}
			exit();

			// Images

			// Sizes
			if (!empty($offer->sizes)) {
				$product['sizes'] = exploded(',', $offer->sizes);
			}

			// Save product
			if ($this->use_combinations === true) {
				// Combinations are used...
				if (array_key_exists($product['name'], $this->products)) {
					// Is a combination of existing product
					if (!array_key_exists('combinations', $this->products[$product['name']])) {
						$this->products[$product['name']]['combinations'] = array();
					}

					$this->products[$product['name']]['combinations'] = array();
					$this->products[$product['name']]['combinations'][] = $product;
				} else {
					// Is a unique product
					$this->products[$product['name']] = $product;
				}
			} else {
				// Combinations are not used...
				$this->products[] = $product;
			}
		}

		unset($this->feed);
	}
}

/**
 * @var FasardiXmlImport $importer
 */
$importer = new FasardiXmlImport();
$importer->execute();
echo '<pre>';
var_dump($importer);
echo '</pre>';
exit();

/*
<offer>
	<id>477_J.SZARY</id>
	<name>Bluza jasnoszara 477</name>
	<url>http://www.fasardi.com/pl/women-best-sellers/bluza-39479.html</url>
	<desc>KOLORY: jasny szary, czarny
ROZMIARY: S  M  L  XL  XXL</desc>
	<cat>MęskaMęska/Wyprzedaż/Bluzy męskie/Bestsellery/</cat>
	<price>69.00</price>
	<oldprice>94.00</oldprice>
	<attrs>
		<attr name="Kolor">Szary</attr>
	</attrs>
	<imgs>
		<img default="true">http://www.fasardi.com/media/catalog/product/m/_/m_6_3.jpg</img>
		<img>http://www.fasardi.com/media/catalog/product/7/7/776_10.jpg</img>
		<img>http://www.fasardi.com/media/catalog/product/7/7/776_12.jpg</img>
		<img>http://www.fasardi.com/media/catalog/product/7/7/776_4.jpg</img>
	</imgs>
	<sizes>M;S</sizes>
	<IsPromoted>1</IsPromoted>
</offer>
*/

/*
$_cats = array();
foreach ($feed->cat as $cat) {
    $_cats[] = $cat;
}
echo '<pre>'.join(',', $_cats).'</pre>';
echo '<pre>'.join(',', $feed->cat).'</pre>';
*/

/*[!]*/echo '<ul>';

foreach ($feed->offer as $offer) {
	/*[!]*/try {
	/*[!]*/	echo '<li>';

	/**
	 * Parse downloaded XML for products and save them.
	 * @todo Each category defined in feed which doesn't exist in our shop must be created!
	 * @todo Some properties (`quentity`, `minimal_quantity`, `meta_keywords`) should be set using configurable default values!
	 */

	/*[!]*/echo 'Processing offer "'.$offer->name.'"!';

	/**
	 * @var array $category IDs of categories of the product.
	 */
	$product_category = import_categories($offer->cat, $default_category_id);

	$product = new Product();
	//$product->ean13 = $offer->id;
	$product->name = create_multi_language_field($offer->name);
	$product->description = create_multi_language_field($offer->desc);
	//$product->description_short = create_multi_language_field('');
	$product->price = floatval($offer->price) * $exchange_rate;
	$product->id_manufacturer = 0;
	$product->id_tax_rules_group = 0;
	$product->id_supplier = 0;
	$product->quantity = 1;
	$product->minimal_quantity = 1;
	$product->additional_shipping_cost = 0;
	$product->wholesale_price = 0;
	$product->ecotax = 0;
	$product->width = 0;
	$product->height = 0;
	$product->depth = 0;
	$product->weight = 0;
	$product->out_of_stock = 0;
	$product->active = 0;
	$product->id_category_default = $default_category_id;
	$product->category = $product_category;
	$product->available_for_order = 0;
	$product->show_price = 1;
	$product->on_sale = 0;
	$product->online_only = 1;
	$product->meta_keywords = create_multi_language_field('fasardi');
	$product->tags = array('fasardi');
	$product->link_rewrite = create_multi_language_field(Tools::link_rewrite($offer->name));
	//$product->id_supplier = 0;
	//$product->supplier_name = '';

	if ($product->save()) {
		$product->add();
	}

	if (!isset($product->id) || (int)$product->id <= 0) {
		continue;
	}

	import_images($product->id, $offer->imgs);

	$product->updateCategories($product->category, true);

	/*[!]*/} catch(Exception $e) {
	/*[!]*/	echo '<br/><code>'.$e->getMessage().'</code>';
	/*[!]*/}
	/*[!]*/	echo '</li>';
}

/*[!]*/echo '</ul>';






// ====================================================================================
?>
<h1>TODO</h1>
<ul style="list-style: none; padding: 0 0 0 0;">
	<li><input type="checkbox" disabled> download feed and save it into <code>docs/xml_import</code>.</li>
	<li><input type="checkbox" disabled> parse downloaded XML file into products.</li>
	<li><input type="checkbox" disabled> log all events (from both phases: downloading and parsing).</li>
</ul>