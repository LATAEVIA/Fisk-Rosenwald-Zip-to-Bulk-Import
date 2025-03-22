# Omeka-S Bulk Upload Plugin

The Omeka-S Bulk Upload Plugin streamlines the process of bulk uploading digital objects and metadata to Omeka-S. Follow these steps to use the plugin after installation:

## Features

- Accepts a zip file containing images and a CSV with image metadata.
- Automates the creation of digital objects in Omeka-S.
- Supports uploading digital objects to local or cloud storage.
- Returns original CSV with each object upload location populated in a new column.

## Prerequisites

Before using this plugin ensure:
- Omeka-S is installed.
- The [Any Cloud module](http://omeka.org/s/modules/AnyCloud) is installed and configured.
- The [CSV Import module](http://omeka.org/s/modules/CSVImport) is installed.

## Installation

1. **Download:** Obtain the plugin by downloading the zip file here.

2. **Installation:**
   - Add the zip to the Omeka-S modules folder and unzip it.
   - Rename the unzipped folder to "ZipImporter" if it's not already named as such.
   - Open a terminal, navigate to the "ZipImporter" folder, and run `composer install`.
      - Sudo or appropriate permissions may be necessary

3. **Update php.ini:**
   - Find your php.ini file with the `php -r "phpinfo();" | grep php.ini` command, or with an info.php page using `<?php phpinfo(); ?>` 
   - Adjust the following fields:
     ```
     upload_max_filesize = 1000M
     max_file_uploads = 100
     post_max_size = 1000M
     ```
   - Restart your server with `service apache2 restart` or the relevant command.
   - Restart php with `systemctl restart php` or the relevant command.

4. **Check PHP Limits:**
   - In the Omeka-S Dashboard, click "System Information" in the lower right to ensure PHP limits are updated.


## Usage

1. **Navigate to "Zip Import" in the Dashboard.**
   - Find the "Zip Import" option in the lower left of the Omeka-S Admin Dashboard.

2. **Upload your zip file.**
   - Click on "Zip Import" and upload your zip file containing images and CSV
      - [Sample of zip](https://github.com/Fisk-University/Fisk-Rosenwald-Zip-to-Bulk-Import/blob/main/GitHub%20Sample%20Upload%20Structure.zip)
      - Note: CSV must be UTF-8 encoded when saved
   - Recommended: Tick the "Automap with simple labels" box.

3. **Verify your mapping and click Import.**
   - Confirm that your mapping is correct.
   - Click the "Import" button to start the bulk upload process.

4. **Upload and Populate:**
   - Relax as the plugin populates your Omeka-S instance and storage.

5. **Verification:**
   - Confirm that the items are successfully uploaded by checking RESOURCES > Items, and by checking your chosen storage location.

6. **OPTIONAL: Download the updated CSV**
   - Go to MODULES > CSV Import > Past Imports to locate the CSV file updated with storage locations.

---

## License

This module is published under the [Server Side Public License (SSPL-1.0)](https://www.mongodb.com/legal/licensing/server-side-public-license).

---

Built with ❤️ by Archatomic & LaTaevia Berry for Fisk University and HBCUs nationwide
