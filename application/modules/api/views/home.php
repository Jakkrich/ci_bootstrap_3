<script type="text/javascript">
$(function () {
	var url = window.location.search.match(/url=([^&]+)/);
	if (url && url.length > 1) {
		url = decodeURIComponent(url[1]);
	} else {
		// Reference: http://petstore.swagger.io/v2/swagger.json
		url = "<?php echo site_url('api/swagger'); ?>";
	}

	// Pre load translate...
	if(window.SwaggerTranslator) {
		window.SwaggerTranslator.translate();
	}

	window.swaggerUi = new SwaggerUi({
		url: url,
		dom_id: "swagger-ui-container",
		supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
		onComplete: function(swaggerApi, swaggerUi){
			if(typeof initOAuth == "function") {
				initOAuth({
					clientId: "your-client-id",
					clientSecret: "your-client-secret-if-required",
					realm: "your-realms",
					appName: "your-app-name",
					scopeSeparator: ",",
					additionalQueryStringParams: {}
				});
			}

			if(window.SwaggerTranslator) {
				window.SwaggerTranslator.translate();
			}

			$('pre code').each(function(i, e) {
				hljs.highlightBlock(e)
			});

			addApiKeyAuthorization();
		},
		onFailure: function(data) {
			log("Unable to Load SwaggerUI");
		},
		docExpansion: "none",
		jsonEditor: false,
		apisSorter: "alpha",
		defaultModelRendering: 'schema',
		showRequestHeaders: false
	});

	function addApiKeyAuthorization(){
		var key = encodeURIComponent($('#input_apiKey')[0].value);
		if(key && key.trim() != "") {
			var apiKeyAuth = new SwaggerClient.ApiKeyAuthorization("api_key", key, "query");
			window.swaggerUi.api.clientAuthorizations.add("api_key", apiKeyAuth);
			log("added key " + key);
		}
	}

	$('#input_apiKey').change(addApiKeyAuthorization);

	<?php
		// if you have an apiKey you would like to pre-populate on the page for demonstration purposes...
		/*
			var apiKey = "myApiKeyXXXX123456789";
			$('#input_apiKey').val(apiKey);
		*/
		if ( !empty($api_key) )
		{
			echo 'var apiKey = "'.$api_key.'"; $("#input_apiKey").val(apiKey);';
		}
	?>

	window.swaggerUi.load();

	function log() {
		if ('console' in window) {
			console.log.apply(console, arguments);
		}
	}
});
</script>

<div id='header'>
	<div class="swagger-ui-wrap">
		<a id="logo" href=""><?php echo $site_name; ?></a>
		<form id='api_selector'>
			<div class='input' style="display:none;"><input placeholder="http://example.com/api" id="input_baseUrl" name="baseUrl" type="text"/></div>
			<div class='input' style="display:none;"><input placeholder="api_key" id="input_apiKey" name="apiKey" type="text"/></div>
			<div class='input' style="display:none;"><a id="explore" href="#" data-sw-translate>Explore</a></div>
		</form>
	</div>
</div>

<div id="message-bar" class="swagger-ui-wrap" data-sw-translate>&nbsp;</div>
<div class="swagger-ui-wrap" data-sw-translate><div class="info_title">BASE URL: <?php echo site_url('api'); ?></div><br/></div>

<div id="swagger-ui-container" class="swagger-ui-wrap"></div>
