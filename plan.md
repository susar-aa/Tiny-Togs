## TinyTogs — Label Printing System

Implement a new **Label Printing System** inside the existing TinyTogs project.

The system must allow the user to manually enter label information while displaying the **Aveeno CheckFresh website as a reference/preview panel on the right side**.

Do not scrape, read, automate, or extract data from the CheckFresh website. The user will manually look at the information displayed there and enter the required values into the TinyTogs printing panel.

---

# 1. Overall Page Layout

Create a dedicated **Label Printing** page with a two-column layout.

### Left side — Label Printing Panel

This is the main interactive panel where the user enters all label information.

### Right side — Aveeno CheckFresh Preview

Display the following website inside a visual web preview area:

`https://www.checkfresh.com/aveeno.html?lang=en`

The purpose of this panel is only to allow the user to manually check an Aveeno batch code and read the displayed date information.

### Important

Do NOT:

* Scrape CheckFresh
* Read its DOM
* Automatically extract data
* Automatically copy data from the website
* Use cross-origin JavaScript to access the website
* Create an API/scraping system for CheckFresh

The user will manually enter the information into the left-side panel.

The right-side CheckFresh panel is only a visual reference.

---

# 2. Left Panel — Label Information

Create the following fields.

### Product Name

A custom text input.

The user should NOT select a product from the existing TinyTogs product database.

When a new product name is entered and used, save the product name into a simple database table for future autocomplete suggestions.

When the user starts typing a product name later:

* Show matching previously entered product names.
* Allow the user to select a suggestion.
* Selecting a suggestion fills the Product Name field.
* Do not create duplicate product names.

Example:

```text
Product Name
[ Aveeno Daily Moisturizing Lotion       ]

Suggestions:
  Aveeno Daily Moisturizing Lotion
  Aveeno Baby Lotion
  Aveeno Skin Relief Lotion
```

Product names are only stored for autocomplete purposes.

Do NOT save individual label-printing sessions.

---

# 3. Manufacture Date

Add:

**Date of Manufacture**

Use a proper date input.

The user will manually enter the manufacture date based on the information they see on the CheckFresh panel.

After entering the manufacture date, display expiry suggestions.

Example:

```text
Date of Manufacture
[ 12/05/2025 ]

Expiry Suggestions:

[ +1 Year ]   [ +2 Years ]   [ +3 Years ]

12/05/2026   12/05/2027   12/05/2028
```

These are only suggestions.

When the user clicks one:

* Automatically fill the Date of Expiry field.
* Allow the user to manually modify the expiry date afterward.

---

# 4. Expiry Date

Add:

**Date of Expiry**

The user can:

* Enter it manually.
* Or click one of the automatically generated expiry suggestions.

Validate that the expiry date cannot be earlier than the manufacture date.

---

# 5. Usable Period After Opening

Add:

**Usable Period After Opening**

This must be a free/custom input.

Examples:

```text
6 Months
12 Months
18 Months
24 Months
30 Days
90 Days
```

The user can type any value.

Do not restrict this field to predefined values.

However, once both Manufacture Date and Expiry Date are entered, provide useful suggestions based on the entered dates.

For example:

```text
Usable Period Suggestions

[ 6 Months ] [ 12 Months ] [ 24 Months ]
```

Each suggestion should have an **Apply** action.

Clicking a suggestion automatically fills the usable-period input.

The user must still be able to enter a completely custom value manually.

---

# 6. Number of Stickers

Add:

**Number of Stickers**

Example:

```text
Number of Stickers
[ 20 ]
```

The user can enter any positive whole number.

When the user clicks Print, the system must generate exactly that number of stickers.

Example:

```text
Number of Stickers = 20
```

→ Print exactly 20 labels.

---

# 7. Label Content

Each sticker must contain ONLY:

```text
PRODUCT NAME

MFD: DD/MM/YYYY
EXP: DD/MM/YYYY
USE WITHIN: [value]
```

Example:

```text
AVEENO DAILY MOISTURIZING LOTION

MFD: 12/05/2025
EXP: 12/05/2028
USE WITHIN: 12 MONTHS
```

Do NOT include:

* SKU
* Product code
* Barcode
* Price
* Brand
* Batch number
* Lot number
* Printing date
* User name
* Database ID
* Any other information

The product name should be the most visually prominent element.

---

# 8. Live Label Preview

Add a **Live Label Preview** to the left panel.

The preview must update immediately whenever the user changes:

* Product Name
* Manufacture Date
* Expiry Date
* Usable Period

The preview must visually represent the actual physical sticker.

Individual sticker dimensions:

**50 mm × 25 mm**

The preview should maintain the correct aspect ratio.

Use the same layout that will be used for printing.

Example:

```text
┌───────────────────────────────┐
│ AVEENO DAILY MOISTURIZING     │
│ LOTION                         │
│                               │
│ MFD: 12/05/2025               │
│ EXP: 12/05/2028               │
│ USE WITHIN: 12 MONTHS         │
└───────────────────────────────┘
       50 mm × 25 mm
```

---

# 9. Zebra Printer / Physical Label

The printer is:

**Zebra ZD230**

Physical sticker size:

* Width: **5 cm / 50 mm**
* Height: **2.5 cm / 25 mm**

There are:

**2 stickers side-by-side in one row.**

Therefore the print layout must be designed around:

```text
┌───────────────────┬───────────────────┐
│                   │                   │
│     LABEL 1       │     LABEL 2       │
│     50 × 25 mm    │     50 × 25 mm    │
│                   │                   │
└───────────────────┴───────────────────┘
```

Do not stretch or distort the labels.

---

# 10. Printing Method

Use:

**Browser Print Dialog**

Do NOT implement direct Zebra printing or ZPL communication.

When the user clicks:

**Print Labels**

the system should:

1. Validate the form.
2. Save the product name to the product-name suggestion table if it is new.
3. Generate the print layout.
4. Generate the required number of stickers.
5. Open the browser's normal print dialog.
6. The user selects the Zebra ZD230 printer.
7. The labels are printed.

The browser print layout must be optimized for the physical label dimensions.

Use print-specific CSS such as `@media print`.

Ensure:

* Correct physical dimensions.
* No unnecessary browser margins.
* No browser headers/footers.
* No unwanted scaling.
* Correct two-label-per-row arrangement.
* Exact requested number of stickers.
* No extra blank labels.
* No clipping of content.

---

# 11. CheckFresh Reference Panel

On the right side of the page, display:

`https://www.checkfresh.com/aveeno.html?lang=en`

Use it ONLY as a visual reference.

The user workflow is:

```text
CheckFresh panel
       ↓
User manually searches/checks batch code
       ↓
User reads date information
       ↓
User manually enters Manufacture Date
       ↓
TinyTogs generates expiry suggestions
       ↓
User selects/enters expiry
       ↓
User enters usable period
       ↓
User enters sticker quantity
       ↓
Print
```

Do not attempt to synchronize the two panels.

If the website cannot be displayed inside an iframe because of its security headers or iframe restrictions, provide a clear **"Open CheckFresh"** button that opens the website in a new browser tab.

Do not attempt to bypass iframe restrictions.

---

# 12. Database

Only create a simple table for previously entered custom product names.

Suggested structure:

```text
label_products

id
product_name
created_at
```

Requirements:

* Product names must be unique.
* Search should be fast.
* Support autocomplete.
* Do not store individual label-printing records.

Do NOT create tables for:

* Manufacture dates
* Expiry dates
* Usable periods
* Print history
* Sticker quantities
* Batch codes

---

# 13. Validation

Before printing:

### Product Name

Required.

### Manufacture Date

Required.

### Expiry Date

Required.

### Usable Period

Required.

### Number of Stickers

Required and must be a positive whole number.

### Date validation

Expiry date cannot be earlier than manufacture date.

Show clear inline validation messages.

Do not reload the page unnecessarily.

---

# 14. UI Design

Follow the existing TinyTogs UI and Bootstrap design system.

Make the page modern, clean, and practical for repeated label printing.

Suggested structure:

```text
┌─────────────────────────────────────────────────────────────────┐
│ Label Printing                                                   │
├───────────────────────────────┬─────────────────────────────────┤
│ LABEL PRINTING                │ AVEENO CHECKFRESH               │
│                               │                                 │
│ Product Name                  │                                 │
│ [.........................]   │                                 │
│                               │                                 │
│ Manufacture Date              │                                 │
│ [.........................]   │     CheckFresh website         │
│                               │                                 │
│ Expiry Date                   │     displayed here              │
│ [.........................]   │                                 │
│ [+1 Year] [+2 Years] [+3 Y]  │                                 │
│                               │                                 │
│ Usable After Opening          │                                 │
│ [.........................]   │                                 │
│ [6 Months] [12 Months]        │                                 │
│                               │                                 │
│ Number of Stickers            │                                 │
│ [........]                    │                                 │
│                               │                                 │
│ LABEL PREVIEW                 │                                 │
│ ┌──────────────────────────┐  │                                 │
│ │ PRODUCT NAME             │  │                                 │
│ │ MFD: DD/MM/YYYY          │  │                                 │
│ │ EXP: DD/MM/YYYY          │  │                                 │
│ │ USE WITHIN: VALUE        │  │                                 │
│ └──────────────────────────┘  │                                 │
│                               │                                 │
│ [       PRINT LABELS       ]  │                                 │
└───────────────────────────────┴─────────────────────────────────┘
```

On smaller screens, make the layout responsive and stack the two sections vertically.

---

# 15. Important Implementation Rules

Before making changes:

1. Inspect the existing TinyTogs project structure.
2. Identify the existing database connection/configuration.
3. Follow the existing coding style.
4. Follow the existing Bootstrap/UI patterns.
5. Reuse existing components where appropriate.
6. Do not modify unrelated TinyTogs modules.
7. Do not replace the existing product system.
8. The label product-name system must remain independent from the existing product database.

Do not introduce unnecessary frameworks or dependencies.

Use the existing project technology and architecture.

All database changes must first be made in the local XAMPP/MySQL database.

After implementing the database change, provide the exact SQL statements required to apply the same change to the production database.

Do not test the system by opening/running it in a browser unless explicitly requested.

---

# 16. Final User Workflow

The final workflow should be extremely simple:

### Step 1

Open **Label Printing**.

### Step 2

Use the CheckFresh panel on the right to manually check the Aveeno batch code.

### Step 3

Enter/select the custom Product Name.

### Step 4

Enter the Manufacture Date manually.

### Step 5

Choose:

```text
+1 Year
+2 Years
+3 Years
```

or manually enter the Expiry Date.

### Step 6

Choose a suggested usable period or enter a custom value.

### Step 7

Enter the number of stickers.

### Step 8

Check the live label preview.

### Step 9

Click:

**PRINT LABELS**

### Step 10

Browser print dialog opens.

User selects the **Zebra ZD230** and prints.

The system must print the exact requested number of **50 mm × 25 mm stickers**, arranged **two stickers per row**.
