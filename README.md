<center>

# Theme agnostic widgets for Elementor

A collection of minimal, lightweight, theme agnostic widgets for Elementor based on core WordPress functions.

![WordPress](https://img.shields.io/badge/WordPress-%23117AC9.svg?style=flat&logo=WordPress&logoColor=white)
![Elementor](https://img.shields.io/badge/Elementor-92003B?logo=elementor&logoColor=fff&style=flat)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=flat&logo=php&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=flat&logo=css3&logoColor=white)
![Creative Commons](https://assets.lpdsgn.it/media/BY-NC-SA.png)

[Widgets](#widgets) •
[How To Use](#how-to-use) •
[Download](#download) •
[Notes](#notes)

![screenshot](https://assets.lpdsgn.it/media/lp-elementor-widgets/Elementor-Widgets-Cover.jpg)

</center>

## Widgets

### Scroll Down Indicator
A simple mouse-looking indicator in pure svg+css
+ Destination ID: lets you specify a css anchor ID to make the indicator linked
+ Dark and light mode

| <img src="https://assets.lpdsgn.it/media/lp-elementor-widgets/scrollDownIndicator_preview_1.jpg" alt="screenshot" width="400"/> | <img src="https://assets.lpdsgn.it/media/lp-elementor-widgets/scrollDownIndicator_preview.gif" alt="screenshot" width="400"/> |
|---|---|
 
### Scroll Down Spinner
A simple rotating text with pure css animation
+ Dimension: lets you specify the dimension of the spinner (in pixels)
+ Dark and light mode

| <img src="https://assets.lpdsgn.it/media/lp-elementor-widgets/scrollDownSpinner_preview_1.jpg" alt="screenshot" width="400"/> | <img src="https://assets.lpdsgn.it/media/lp-elementor-widgets/scrollDownSpinner_preview.gif" alt="screenshot" width="400"/> |
|---|---|

### Breadcrumbs
Display breadcrumbs using the WordPress core functions to retrieve titles and URLs

![screenshot](https://assets.lpdsgn.it/media/lp-elementor-widgets/Breadcrumbs-preview-3.jpg)
+ Support for custom separator character
+ Support for displaying:
  - **Homepage**
  - **Archive**:<br>works with custom post types' archive (if existing) and blog's archive (if a page is set)
  - **Parent category**
  - **Child categories**
  - Ability to set the maximum number of child categories displayed (useful if more than one is set for the post)
+ Support for custom color and custom typography

| <img src="https://assets.lpdsgn.it/media/lp-elementor-widgets/Breadcrumbs-preview-1.1.jpg" alt="screenshot" width="400"/> | <img src="https://assets.lpdsgn.it/media/lp-elementor-widgets/Breadcrumbs-preview-2.1.jpg" alt="screenshot" width="400"/> |
|---|---|


## How To Use

Clone this repository inside your theme's folder. From your command line:
```bash
# Clone this repository
$ git clone https://github.com/LPdsgn/lp-elementor-Widgets.git
```

If you download as a `.zip` archive instead, **remove** the version number (e.g. `-1.0.1`) from the folder's name
> `lp-elementor-widgets-1.0.1` → `lp-elementor-widgets`

Then add this line of code to your `functions.php` file to load the widgets:
```php
/* CUSTOM ELEMENTOR WIDGET 
 * qui i widget custom registrati in Elementor
 */
include_once(get_stylesheet_directory() . '/widgets/components.php');
```

## Download

You can [download](https://github.com/LPdsgn/LP-Elementor-Widgets/releases/) the latest version of directly as a `.zip` archive.

## Notes

This software was made upon the following resources:

- [Elementor Widgets](https://developers.elementor.com/docs/widgets/)
- [Creating Your First Addon](https://developers.elementor.com/docs/getting-started/first-addon/)

## License

GPL >3.0


> [lpdsgn.it](https://lpdsgn.it) • 
> GitHub [@LPdsgn](https://github.com/LPdsgn) • 
> [LinkedIn](https://www.linkedin.com/in/luigi-pedata-lpdsgn/) • 
> [Instagram](https://www.instagram.com/lpdsgn.it/)

