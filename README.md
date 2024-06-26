# SEE FORK! Omeka-S Bulk Upload Plugin

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

3. **Update php.ini:**
   - Find your php.ini file with `php -r "phpinfo();" | grep php.ini`.
   - Adjust the following fields:
     ```
     upload_max_filesize = 1000M
     max_file_uploads = 100
     post_max_size = 1000M
     ```
   - Restart your server with `service apache2 restart` or the relevant command.

4. **Check PHP Limits:**
   - In the Omeka-S Dashboard, click "System Information" in the lower right to ensure PHP limits are updated.


## Usage

1. **Navigate to "Zip Import" in the Dashboard.**
   - Find the "Zip Import" option in the lower left of the Omeka-S Admin Dashboard.

2. **Upload your zip file.**
   - Click on "Zip Import" and upload your zip file containing images and CSV
      - [Sample of zip](https://github.com/LATAEVIA/Fisk-Rosenwald-Zip-to-Bulk-Import/blob/main/GitHub%20Sample%20Upload%20Structure.zip)
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

## Contributing to Fisk-Rosenwald-Zip-to-Bulk-Import

Thank you for considering contributing to the Fisk-Rosenwald-Zip-to-Bulk-Import! Contributions help improve and grow the functionality of the plugin.

### Reporting Issues

Before May 2025:
If you encounter any issues, have questions, or want to suggest enhancements, please open an issue on the [GitHub Issues page](https://github.com/LATAEVIA/Fisk-Rosenwald-Zip-to-Bulk-Import/issues). Provide detailed information about the problem, including steps to reproduce it.

**After May 2025:**
If you encounter any issues, have questions, or want to suggest enhancements, unfortunatly this project will be closed and no further edits will be made on our end. We encourage you to take the reins with a clone or fork of your own.


### Feature Requests

Feel free to open an issue if you have a feature request. Describe the new functionality you're proposing and how it would benefit users.

### Pull Requests

We GREATLY welcome pull requests that address issues or bring new features to the Fisk-Rosenwald-Zip-to-Bulk-Import. To submit a pull request:

1. Fork the repository to your GitHub account.
2. Create a new branch for your changes.
3. Make your changes and commit them with clear messages.
4. Push your branch to your fork.
5. Open a pull request on the [GitHub Pull Requests page](https://github.com/LATAEVIA/Fisk-Rosenwald-Zip-to-Bulk-Import/pulls).

### Coding patterns

We have yet to set any standards/patterns, so just keep it clean and consistant. If your pull request is related to an existing issue, reference the issue number in your pull request description.

### Code of Conduct

Kindness, respect, love. 

Thank you for contributing!

## License

This project is licensed under the [GNU General Public License version 3.0 (GPL-3.0)](https://opensource.org/licenses/GPL-3.0). See the [LICENSE.md](LICENSE.md) file for details.
