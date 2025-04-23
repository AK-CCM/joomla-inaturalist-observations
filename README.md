# Joomla Module: iNaturalist Observations

This Joomla 4/5 module displays recent observations from a specific [iNaturalist](https://www.inaturalist.org) user filtered by taxon (e.g., Fungi). It's privacy-friendly (no embedded iframes) and supports thumbnail images, common names, and observation dates.

<!-- ![Example Screenshot](screenshot.png) Optional -->

## 🌿 Features

- Uses iNaturalist public API
- Filters by `user_id` and `taxon_id`
- Displays thumbnails and basic metadata
- Uses local caching to reduce API requests
- No iframe = better GDPR compliance

## 📦 Installation

1. Download the contents of the `mod_inaturalist_observations` folder as a ZIP file.
2. In Joomla Admin:  
   **Extensions → Manage → Install** → Upload your ZIP.
3. Go to **Extensions → Modules**, find **“iNaturalist Observations”**, and assign it to a position and menu.

## 🔧 Parameters

| Parameter | Description |
|----------|-------------|
| `User ID` | Your iNaturalist username |
| `Taxon ID` | iNaturalist Taxon ID (e.g., 47170 = Fungi) |
| `Count` | Number of recent observations to show |
| `Cache Duration` | Time in seconds to keep API data cached |

## ✅ Example

Show 5 recent fungal observations by user `ak_ccm`:

```bash
User ID: ak_ccm
Taxon ID: 47170
Count: 5
Cache Duration: 86400
