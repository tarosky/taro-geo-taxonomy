# Taro Geo Taxonomy

Contributors: Takahashi_Fumiki,tarosky  
Tags: Addrress, Japan, Map  
Requires at least: 5.0  
Requires PHP: 7.2  
Tested up to: 6.0  
Stable tag: nightly

WordPress plugin to create geometric taxonomy.

## Description

This plugin has mainly 3 features.

* Add Zip search endpoint availabel from post edit screen. If you are experienced developer.
* Create area taxonomy. It covers all Japanese city.
* Add geo location for each post.

## Installation

See development section and build plugin files. Then upload it to your `wp-content/plugins` directory.
`node_modules` directory is not necessary.

### Development

- Clone this repository.
- Do `comopser install`. If you don't have comopser, intstall it.
- Install `npm install`. If you don't have npm, install it.
- Run `npm start`. All assets will be build with gulp.

If you want to develop locally, type `npm run watch`.
Gulp will watch your changes.

##  Changelog 

### 1.1.2

* Now you can enter Google Map API key from option screen.

### 1.1.1

* Fix readme.

### 1.1.0

* Fix timeout bug.
* Remove bower

### 1.0

* First release