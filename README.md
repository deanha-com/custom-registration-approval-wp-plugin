# custom-registration-approval

Vibe coded using Perplexity Pro, this plugin is made for Urban Merchants Wholesale Portal

# Custom Registration Approval – Wholesale Portal Plugin

Robust, modular WordPress plugin for wholesale portals requiring user application, account approval, user management, and protected access to WooCommerce.  
Designed for high security, modularity, and easy extension.

---

## Features

- Secure custom registration with validation (multistep or classic)
- Company/identity confirmation via API (company number, name, status check)
- Manual or automatic approval workflow with admin UI for pending users
- Modular user meta management (company info, delivery address, etc.)
- My Account: Editable custom fields, plus dedicated “Company Details” tab
- Restrict product/pricing/cart/shop visibility to logged-in/approved users only
- Email notification and success messaging (inline, no redirects)
- Fully extensible, well-commented code

---

## Directory Structure



# File Structure (Main)

```
custom-registration-approval/
├── custom-registration-approval.php                 # Main plugin file (bootstrap)
├── includes/
│   ├── form-shortcode.php                           # [custom_registration_form] markup/logic
│   ├── form-multistep.php                           # [cra_multistep_registration_form]
│   ├── ajax-company-validator.php                   # Company API AJAX check endpoint
│   ├── validation.php                               # All validation & user save logic
│   ├── approval.php                                 # Admin approval UI
│   ├── restrict-guest-access.php                    # Shop access control for guests
│   ├── restricted-screen.php                        # Shortcode for access block screen
│   └── user-meta-profile.php                        # Profile/company info for admin+account
├── assets/
│   ├── style.css                                    # Registration form styling
│   ├── multistep.css                                # Multistep/progress form css
│   └── multistep.js                                 # Multistep registration JS
└── README.md
```
---

## Installation & Setup

### 1. Place Files

Copy the `/custom-registration-approval/` folder (with all `/includes/` and `/assets/` subfolders) to your WordPress `/wp-content/plugins/`.

### 2. Activate Plugin

Go to **WP Admin > Plugins** and activate **Custom Registration Approval**.

### 3. Create Required Pages & Shortcodes

- **Registration form:**  
  Create a page (e.g., `/register/`) 
  and add: `[custom_registration_form]`
  or for multistep: `[cra_multistep_registration_form]`

  
- **Wholesale access (guest block):**  
Create a page (e.g., `/wholesale-access/`) and add:  
`[cra_restricted_screen]`


### 4. WooCommerce My Account Integration

- A dedicated **Company Details** tab will appear in "My Account."  
All custom fields are editable here (or read-only, with tooltips if required).

### 5. Permalink Flush (Important!)

After **activating the plugin or adding new My Account custom tabs/endpoints**,  
**Go to:**  
`WP Admin → Settings → Permalinks`  
and click **Save Changes** to flush rewrite rules and enable custom My Account tabs.  
*(If you skip this, tabs like `/my-account/company-details/` will 404!)*

### 6. Admin Approval UI

- Go to **Users > Registration Approvals** in wp-admin to approve/pending/reject applications.

---

## Developer Notes

- All custom field logic is modular in `/includes/user-meta-profile.php`.
- Registration validation and flow is in `/includes/validation.php`.
- Company/VAT API logic: `/includes/validation.php` + `/includes/ajax-company-validator.php`.
- Guest/shop restrictions managed in `/includes/restrict-guest-access.php` and `/includes/restricted-screen.php`.
- Add or edit required/optional fields in registration markup and validation as requirements evolve.
- For new endpoints (like the “Company Details” tab), always flush permalinks after deploy.

---

## Customization & Extending

- **To add/remove user meta fields:** Update `/includes/user-meta-profile.php` and registration forms.
- **To modify approval workflow or admin UI:** Update `/includes/approval.php`.
- **For AJAX or advanced UX:** Extend `/assets/multistep.js` or add new shortcodes as needed.

---

## Security Reminders

- All input sanitized/validated (server-side).
- Only admin users can approve/reject applicants.
- No direct editing of plugin core files after deployment—update via overrides/includes only.

---

## Troubleshooting

- **New My Account tab is a 404:**  
Flush permalinks: **Settings > Permalinks > Save Changes**
- **Shop/prices visible to guests:**  
Ensure `/includes/restrict-guest-access.php` is loaded and `$reg_url` points to your guest info page.
- **Fields missing on user/profile edit:**  
Check `/includes/user-meta-profile.php` for correct field/option setup and meta keys.
- **Registration errors:**  
Confirm required fields and validation match form names and meta keys; see validation.php for details.

---

Happy onboarding your wholesaler customers!
