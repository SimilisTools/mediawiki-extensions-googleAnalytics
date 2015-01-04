<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

//self executing anonymous function to prevent global scope assumptions
call_user_func( function() {

	$GLOBALS['wgExtensionCredits']['other'][] = array(
		'path'           => __FILE__,
		'name'           => 'Google Analytics Integration',
		'version'        => '2.2.0',
		'author'         => 'Tim Laqua, Toni Hermoso',
		'descriptionmsg' => 'googleanalytics-desc',
		'url'            => 'https://www.mediawiki.org/wiki/Extension:Google_Analytics_Integration',
	);

	$GLOBALS['wgMessagesDirs']['googleAnalytics'] = __DIR__ . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['googleAnalytics'] = dirname(__FILE__) . '/googleAnalytics.i18n.php';

	$GLOBALS['wgHooks']['SkinAfterBottomScripts'][]  = 'efGoogleAnalyticsHookText';
	$GLOBALS['wgHooks']['ParserAfterTidy'][] = 'efGoogleAnalyticsASAC';

	$GLOBALS['wgGoogleAnalyticsAccount'] = "";
	$GLOBALS['wgGoogleAnalyticsAddASAC'] = false;

	// https://support.google.com/analytics/answer/2558867?hl=en
	$GLOBALS['wgGoogleAnalyticsLinkAttr'] = true;

	// https://developers.google.com/analytics/devguides/collection/gajs/gaTrackingSite
	$GLOBALS['wgGoogleAnalyticsSetDomain'] = "";

	// New Universal: https://developers.google.com/analytics/devguides/collection/upgrade/reference/gajs-analyticsjs
	$GLOBALS['wgGoogleAnalyticsUniversal'] = true;

	// These options are deprecated.
	// You should add the "noanalytics" right to the group
	// Ex: $wgGroupPermissions["sysop"]["noanalytics"] = true;
	// Default not analytics for sysops
	$GLOBALS['wgGroupPermissions']["sysop"]["noanalytics"] = true;
	$GLOBALS['wgGoogleAnalyticsIgnoreSysops'] = true;
	$GLOBALS['wgGoogleAnalyticsIgnoreBots'] = true;

});

function efGoogleAnalyticsASAC( &$parser, &$text ) {
	global $wgOut, $wgGoogleAnalyticsAccount, $wgGoogleAnalyticsAddASAC;

	if( !empty($wgGoogleAnalyticsAccount) && $wgGoogleAnalyticsAddASAC ) {
		$wgOut->addScript('<script type="text/javascript">window.google_analytics_uacct = "' . $wgGoogleAnalyticsAccount . '";</script>');
	}

	return true;
}

function efGoogleAnalyticsHookText( $skin, &$text='' ) {
	$text .= efAddGoogleAnalytics();
	return true;
}

function efAddGoogleAnalytics() {
	global $wgGoogleAnalyticsAccount;
	global $wgGoogleAnalyticsIgnoreSysops, $wgGoogleAnalyticsIgnoreBots;
	global $wgGoogleAnalyticsUniversal, $wgGoogleAnalyticsLinkAttr, $wgGoogleAnalyticsSetDomain;
	global $wgUser;
	
	if ( $wgUser->isAllowed( 'noanalytics' ) ||
		 $wgGoogleAnalyticsIgnoreBots && $wgUser->isAllowed( 'bot' ) ||
		 $wgGoogleAnalyticsIgnoreSysops && $wgUser->isAllowed( 'protect' ) ) {
		return "\n<!-- Google Analytics tracking is disabled for this user -->";
	}

	if ( $wgGoogleAnalyticsAccount === '' ) {
		return "\n<!-- Set \$wgGoogleAnalyticsAccount to your account # provided by Google Analytics. -->";
	}
	
	
	$linkAttr = "";
	if ( $wgGoogleAnalyticsLinkAttr ) {
		if ( $wgGoogleAnalyticsUniversal ) {
			$linkAttr = "ga('require', 'linkid', 'linkid.js');";
		} else {
			$linkAttr = "var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
_gaq.push(['_require', 'inpage_linkid', pluginUrl]);";
		}
	}
	
	$subDomain = "";
	if ( !empty( $wgGoogleAnalyticsSetDomain ) ) {
		if ( $wgGoogleAnalyticsUniversal ) {
			$subDomain = "{'cookieDomain': '".$wgGoogleAnalyticsSetDomain."'}";
		} else {
			$subDomain = "_gaq.push(['_setDomainName', '".$wgGoogleAnalyticsSetDomain."']);
_gaq.push(['_setAllowLinker', true]);
_gaq.push(['_setAllowHash', false]);";
		}
	} else {
		if ( $wgGoogleAnalyticsUniversal ) {
			$subDomain = "'auto'";
		}
	}

	if ( $wgGoogleAnalyticsUniversal ) {
	return <<<HTML
<script>
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

ga('create', '{$wgGoogleAnalyticsAccount}', {$subDomain});
{$linkAttr}
ga('send', 'pageview');

</script>
HTML;
	} else {

	return <<<HTML
<script type="text/javascript">
var _gaq = _gaq || [];
var _gaq.push(['_setAccount', '{$wgGoogleAnalyticsAccount}']);
{$linkAttr}
{$subDomain}
_gaq.push(['_trackPageview']);
(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
HTML;
	}
}

///Alias for efAddGoogleAnalytics - backwards compatibility.
function addGoogleAnalytics() { return efAddGoogleAnalytics(); }
