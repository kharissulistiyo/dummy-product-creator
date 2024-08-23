# Dummy Product(s) Creator for WooCommerce
Simple tool for WooCommerce store manager to easily create custom dummy products.

### Installation

1. Download the .zip package of this plugin
2. Install to your WordPress site via plugins uploader menu or upload manually to `plugins` directory via your File Manager

### Purpose

This plugin aims to help shop manager to do an easy dummy products creation. You can create 200 dummy products at once using particular mechanism mentioned in FAQs section below.

### FAQs

**Q: How to specify the dummy products data?**
**A:** Edit the plugin's file: `wp-content/plugins/dummy-product-creator/dummy-data/dummy-prods-1.json`

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
**A:** Firstly, you'd need to create the dummy products creator form to any of your page with this shortcode:

```
[dpc_form_ui]
```

The form display would look like this:

![Screenshot 2024-08-23 at 21 46 54](https://github.com/user-attachments/assets/efd323d7-86d4-4508-889a-54ddb6b640a9)

Select desired option and just the `Create` button.

Please note that the form's elements style may depend on current active theme's stylesheet.

**Q: Can I import dummy products from Google Sheets?**
**A:** Yes! To help you get started, you may copy [this sheets sample](https://docs.google.com/spreadsheets/d/1m4BEyoErivDbUAkll37Guax1qVfcQFLV7enIQhJyN44/edit?usp=sharing). Ensure you don't do changes for the values of the first row's columns. Then, make your items addition necessarily. Before importing with the form, convert the sheets to JSON by following [this instruction](https://help.cloud.just-ai.com/en/aimylogic/publication_channels/google_tables/csv_to_json/). Copy the entire JSON content, paste into `dummy-prods-1.json` (or any desired file), and run import process using the provided form.

**Q: What product's data are supported?**
**A:** Currently the plugin only supports product title and price. Other product data will be supported in the future version update.