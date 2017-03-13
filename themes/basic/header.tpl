<!DOCTYPE html>
<html lang="{{ config.site.lang }}">
	<head>
		<meta charset="utf-8">
		<meta name="author" content="{{ config.site.author }}">
		<meta name="description" content="{{ config.site.description }}">
		<meta name="keywords" content="{{ config.site.keywords }}">
		<title>{{ config.site.name }} - {{ page.title }}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="http://getbootstrap.com/dist/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container">
			<nav class="navbar navbar-default">
				<div class="container-fluid">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
						<a class="navbar-brand" href="#">{{ config.site.name }}</a>
					</div>
					<div id="navbar" class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							{% for name in config.menu %}
							<li><a href="{{ index }}">{{ name }}</a></li>
							{% endfor %}
						</ul>
					</div>
				</div>
			</nav>
			