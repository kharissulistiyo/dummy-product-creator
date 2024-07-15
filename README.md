# Dummy Products Creator for WooCommerce
Simple tool for WooCommerce store manager to easily create custom dummy products.

### Installation:

1. Download the .zip package of this plugin
2. Install to your WordPress site via plugins uploader menu or upload manually to `plugins` directory via your File Manager

### Purpose:

This plugin aims to help shop manager to do an easy dummy products creation. You can create 200 dummy products at once using particular mechanism mentioned in FAQs section below.

### FAQs:

**Q: How to specify the dummy products data?**
**A:** Edit the plugin's file: `wp-content/plugins/Dummy-Product-Creator/dummy-data/dummy-prods-1.json`

Add your product data into that file. Follow this standard JSON format:

```
[
    {
      "title": "Kopi",
      "price": 50000
    },
    {
      "title": "Minyak",
      "price": 7000
    },
    {
      "title": "Tepung",
      "price": 3000
    }
]
```

**Q: How to run dummy products importer?**
**A:** Add this URL parameter to any of you page link: `?dpc_run_importer=yes`
Example: https://yourshop.com/?dpc_run_importer=yes

Whith this link, the importer will automatically run once the URL loaded in your browser.

To choose which dummy products to import, use `dpc_store` parameter. Example: `?dpc_store=store-2`.

You can see list of currently accepted values for `dpc_store` parameter in this array variable (that is found in the `dpc_json_files_array()` function of main plugin's file):

```
$store_products  = array( 
    'store-1' => 'dummy-prods-1.json',
    'store-2' => 'dummy-prods-2.json',
);
```

To import dummy products from all available JSON files, use `?dpc_store=all`. Example: https://yourshop.com/?dpc_run_importer=yes&dpc_store=all

If the `dpc_store` parameter not set, dummy-prods-1.json will be used.

To set whether products drafter or published, use `status` parameter. Example: `?status=draft`. The only accepted value for this parameter: `publish` or `draft`. If the `status` parameter not set, it will use `publish`.


**Q: Can I import dummy products from Google Sheets?**
**A:** Yes! To help you get started, you may copy [this sheets sample](https://docs.google.com/spreadsheets/d/1m4BEyoErivDbUAkll37Guax1qVfcQFLV7enIQhJyN44/edit?usp=sharing). Ensure you don't do changes for the values of the first row's columns. Then, make your items addition necessarily. Before importing, convert the sheets to JSON by following [this instruction](https://help.cloud.just-ai.com/en/aimylogic/publication_channels/google_tables/csv_to_json/). Copy the entire JSON content, paste into `dummy-prods-1.json` (or any desired file), and run import process with proper URL parameters mentioned above.

**Q: What product's data are supported?**
**A:** Currently only product title and price.