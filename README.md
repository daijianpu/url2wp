# url2wp
url2wp is a high-performance, surgical-grade utility designed for WordPress developers and power users who need to integrate external media into the native Media Library without physical file ingestion.

============================================================
PLUGIN NAME: 囧丁乙 - url2wp (External Media Linker)
VERSION: 2.0.0
AUTHOR: EEZZ (囧丁乙 Team)
============================================================

### 1. OVERVIEW
url2wp is a high-performance, surgical-grade utility designed for WordPress developers and power users who need to integrate external media into the native Media Library without physical file ingestion. 

Unlike standard "Import" tools that download files to your server, url2wp maps remote URLs directly to the WordPress database. This preserves local storage, bypasses certain hotlinking restrictions, and ensures that your Media Library remains lean and fast.

### 2. KEY FEATURES
* **Zero-Storage Footprint:** Attach remote images/media to your library using their original URLs as the source (GUID).
* **Bulk Processing:** Submit multiple URLs simultaneously; the processor handles detection and metadata generation in one pass.
* **Intelligent Detection:** Built-in support for PHP `@getimagesize` to automatically resolve image dimensions and MIME types.
* **Manual Overrides:** Precise manual input fields for Width, Height, and MIME type when remote servers block automated probing.
* **Duplicate Prevention:** A robust check mechanism prevents redundant database entries for the same URL.
* **Native UI Integration:** Injects seamlessly into the WordPress Media Grid and provides a dedicated standalone management page.
* **Linux-Optimized Architecture:** Built with a strict lowercase autoloader and OOP structure to ensure stability across all hosting environments.

### 3. HOW IT WORKS (THE LOGIC)
The plugin utilizes a "Scalpel Approach" to hook into the WordPress core. It tags external media with a specific meta key (`_eezz_is_external`). When WordPress requests the file path via `get_attached_file`, the plugin intercepts the request and redirects it to the remote URL. This ensures that the WordPress UI treats the external link as a native attachment without requiring the file to exist on your disk.

### 4. USAGE INSTRUCTIONS

#### A. Accessing the Interface
There are two ways to access the tool:
1.  **Media Library (Grid Mode):** Navigate to `Media > Add New`. You will see an "Add External Media" button integrated into the standard upload UI.
2.  **Dedicated Menu:** Navigate to `Media > Add External Media` in the sidebar for a full-page processing interface.

#### B. Adding Media
1.  **Input URLs:** Paste your remote media URLs into the textarea (one URL per line).
2.  **Automatic Detection:** Click "Add". The system will attempt to ping the remote server to fetch dimensions and file types automatically.
3.  **Result Logs:** View the real-time log. 
    * ✅ Indicates the media was successfully mapped.
    * ❌ Indicates a detection failure (usually due to remote server firewalls).

#### C. Handling Failures (Manual Mode)
If a URL fails (❌), the "Manual Properties" panel will appear:
1.  Enter the specific **Width** and **Height** of the image.
2.  Enter the **MIME Type** (e.g., `image/jpeg`).
3.  Click "Add" again to force the database entry using these manual specifications.

#### D. Clearing Data
Use the "Clear" button to reset the UI and log. Note that successfully added media will remain in your Media Library even after clearing the input box.

### 5. TECHNICAL REQUIREMENTS
* **WordPress:** 5.0 or higher.
* **PHP:** 7.4 or higher.
* **Server Permissions:** The server must allow outbound HEAD/GET requests (standard for most hosts) for automatic dimension detection.

### 6. IMPORTANT NOTES
* **Hotlinking:** This plugin relies on the remote server's permission to display the image. If the source server employs strict Referer-checking, the image may not render on your site.
* **Surgical Stability:** This plugin modifies how `get_attached_file` functions *only* for media created by this tool. It does not interfere with your existing physical uploads.
============================================================
