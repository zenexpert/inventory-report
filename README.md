# Inventory Report for Zen Cart

A modernized and encapsulated version of the classic Inventory Report plugin for Zen Cart. This tool provides store owners with a comprehensive, sortable overview of their stock levels and total inventory value.

This version has been entirely rewritten to support Zen Cart 2.1.0, PHP 8.x, and MySQL 8.x strict mode.

## Requirements
* Zen Cart 2.0.0 or newer
* PHP 8.x
* MySQL 5.7+ or MariaDB 10.2+

## Features
* **Modern UI:** Fully integrated with Zen Cart's native Bootstrap 3.4.1 admin template for a responsive layout.
* **Calculated Inventory Value:** Automatically calculates and displays the total monetary value of your stock per item and per page.
* **Dynamic Filtering:** Filter your inventory report by Master Category.
* **Active/Inactive Toggle:** Easily exclude inactive items from your report. When included, inactive items are marked with accessible FontAwesome indicators.
* **Low Stock Highlighting:** Items with a quantity of 0 or less are automatically highlighted in red.
* **Sortable Columns:** Click any column header to sort the report by ID, Model, Name, Quantity, Price, or Total.
* **Clean CSV Export:** Export your current view (respecting your category and status filters) to a clean CSV file, with proper decoding to prevent HTML/currency entity corruption in spreadsheet software.

## Installation

This plugin utilizes Zen Cart's Encapsulated Plugin architecture, meaning it will not overwrite any core files.

1. Unzip the downloaded archive.
2. Upload the `zc_plugins/InventoryReport` folder to the `zc_plugins` directory on your server.
3. Log in to your Zen Cart Admin.
4. Navigate to **Modules > Plugin Manager**.
5. Locate **Inventory Report** in the list and click **Install**.

## Usage

Once installed, you can access the report by navigating to **Reports > Inventory Report** in your Zen Cart admin menu.

* **Sorting:** Click on the table headers to sort the data ascending or descending.
* **Filtering:** Use the dropdown menu at the top right to view a specific category.
* **Toggle Inactive:** Click the "Exclude Inactive Items" / "Include Inactive Items" button to toggle the visibility of disabled products.
* **Exporting:** Click "Export to CSV" to download the data currently queried on your screen.

## Technical Notes & Upgrades
* If you're using an older version of this plugin, make sure you remove all files from the server.

##  License
Portions Copyright 2003-2026 Zen Cart Development Team.
Released under the **GNU Public License V2.0**.
