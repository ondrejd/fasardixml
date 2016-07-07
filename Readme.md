# Fasardi XML

Modul pro __[Prestashop 1.6.x](https://www.prestashop.com/)__, který umožňuje importovat produkty z e-shopu [Fasardi](http://www.fasardi.com/) pomocí XML.

## Úkoly

* [ ] nastavení (přes stránku `Moduly` v aministraci, viz. [Adding a configuration page](http://doc.prestashop.com/display/PS16/Adding+a+configuration+page)):
	* [ ] kurz použitý pro převod Zlotý na Kč
	* [ ] URL datového XML souboru
	* [ ] checkbox Ano/Ne, zda použít `multi_language_fields` (tzn. pro všechny existující lokalizace je uložena hodnota ze zdrojového XML) či zda jen s defaultním jazykem (tzn. `single_language_fields`).
	* [ ] defaultní kategorie (nastavit pomocí _PrestaShop Radio Tree_).
	* [ ] nastavení, která data přepisovat a která ne.
* [ ] vytvořit kód, který provede samotný import (převod XML do produktů a jejich uložení do DB). Při možném přepisování produktů se chová dle nastavení. Cena ve Zlotých je převedena na Kč.
* [ ] umožnit výše vytvořený kód spouštět pravidelně přes CRON službu _PS_
* [ ] (__5.7.2016__) publikovat na serveru [Modnia.cz](Modnia.cz)


## Nastavení serveru [Modnia.cz](Modnia.cz)

### FTP

|        | Hodnota                           |
|--------|-----------------------------------|
| Server | 84397.w97.wedos.net               |
| Login  | w84397_xml                        |
| Heslo  | S39BgeJH                          |

### Administrace

|       | Hodnota                           |
|-------|-----------------------------------|
| URL   | http://www.modnia.cz/acxpro4589   |
| Login | bruzek@stafox.cz                  |
| Heslo | XwrRm4573E                        |


## odkazy

- [Hooks in PrestaShop 1.5](http://doc.prestashop.com/display/PS15/Hooks+in+PrestaShop+1.5)
- [PrestaShop 1.6 Developer Guide](http://doc.prestashop.com/display/PS16/Developer+Guide)
- [PrestaShop Forum - Core Developers - programmatically adding product](https://www.prestashop.com/forums/topic/262781-programmatically-adding-product/)
- [PrestaShop Forum - Core Developers - Custom Product Importer](https://www.prestashop.com/forums/topic/89269-custom-product-importer/?p=502506)
- [PrestaShop Forum - Core Developers - [SOLVED]Add Images programmatically](https://www.prestashop.com/forums/topic/269006-solvedadd-images-programmatically/)
- [StackOverFlow / Create product from a module in prestashop](http://stackoverflow.com/questions/6385695/create-product-from-a-module-in-prestashop)
- [StackOverFlow / prestashop a new product with features and images through a module](http://stackoverflow.com/questions/29840813/prestashop-a-new-product-with-features-and-images-through-a-module?rq=1)
- [StackOverFlow / How to add Product using module in prestashop](http://stackoverflow.com/questions/21498661/how-to-add-product-using-module-in-prestashop)
- [StackOverFlow / add category programmatically prestashop](http://stackoverflow.com/questions/18720880/add-category-programmatically-prestashop)
- [StackOverFlow / How to add image during programmatic product import in prestashop?](http://stackoverflow.com/questions/28447131/how-to-add-image-during-programmatic-product-import-in-prestashop)
- [StackOverFlow / PRESTASHOP 1.6 Add a category to a product programmatically](http://stackoverflow.com/questions/26777620/prestashop-1-6-add-a-category-to-a-product-programmatically)
- [StackOverFlow / Prestashop product image is not uploaded when product is created programmatically](http://stackoverflow.com/questions/32758456/prestashop-product-image-is-not-uploaded-when-product-is-created-programmaticall)
- [GitHub.com - PrestaShop/cronjobs](https://github.com/PrestaShop/cronjobs)
