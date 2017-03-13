# Flaty

## Description

Flaty is Web-platform, built from scratch as a CMS. it's extendable and customizable. With a very small amount of configuration, you’ll find yourself with a very personalized experience. and with a little bit of coding knowledge will get you a long way.

It's built with minimum amount of files(~7), and no composer autoloading.

## Requierements

* PHP >= 5.6

## Setup

Just download the zip and run your server on `public` folder.

## Config

there is one main config file `config.json` with 3 parts:
* the site options which contains site's general information.
* Menu containing the url: name and used for generating the menu.
* Plugin: Set the autostarting plugins.

## QuickStart

create files with `.md` extension inside the `content` folder.

**Example:** `text-page.md` the url will be `www.yoursite.com/test-page`

### Page content

the page content is seperated in two parts by `---`

the first part is the page config, generaly taking the form of `key=value` and the second is the page content which is written in HTML. 

**Example** 

```
title=Test page
---
<b>Welcome</b>
<p>
	cntent of test page
</p>
```

### Themes

themes are written by a pseudo PHP code, that is kinda Similar to Twig, but not as fully featured.

