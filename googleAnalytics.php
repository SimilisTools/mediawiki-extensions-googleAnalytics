<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is a MediaWiki extension, it is not a valid entry point' );
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'Google Analytics Integration',
	'version'        => '2.1',
	'author'         => 'Tim Laqua, Toni Hermoso',
	'descriptionmsg' => 'googleanalytics-desc',
	'url'            => 'https://github.com/SimilisTools/mediawiki-extensions-googleAnalytics',
);

$wgExtensionMessagesFiles['googleAnalytics'] = dirname(__FILE__) . '/googleAnalytics.i18n.php';

$wgHooks['ParserBeforeTidy'][] = 'wgAddGoogleAnalytics';

$wgGoogleAnalyticsAccount = "";
$wgGoogleAnalyticsSubDomain = "";
$wgGoogleAnalyticsIgnoreSysops = true;
$wgGoogleAnalyticsIgnoreBots = true;


function wgAddGoogleAnalytics( &$parser, &$text ) {

	global $wgGoogleAnalyticsAccount, $wgGoogleAnalyticsIgnoreSysops, $wgGoogleAnalyticsIgnoreBots, $wgGoogleAnalyticsSubDomain;
	
	// Let's get user
	$user = $parser->getUser();
	
	//Output code
	$code = "";
	// Whether skip the code
	$skip = false;
	
	if ( $user->isAllowed( 'bot' ) && $wgGoogleAnalyticsIgnoreBots ) {
		$code = "\n<!-- Google Analytics tracking is disabled for bots -->";
		$skip = true;
	}

	if ( $user->isAllowed( 'protect' ) && $wgGoogleAnalyticsIgnoreSysops ) {
		$code = "\n<!-- Google Analytics tracking is disabled for users with 'protect' rights (I.E. sysops) -->";
		$skip = true;
	}

	// Let's put info of DomainName
	$domainName = "";
	if ( $wgGoogleAnalyticsSubDomain  !== '' ) {
		$domainName = "_gaq.push(['_setDomainName', '".$wgGoogleAnalyticsSubDomain."']);";
	}

	// Account
	$gaAccount = "";
	
	if ( $wgGoogleAnalyticsAccount === '' ) {
		$code = "\n<!-- Set \$wgGoogleAnalyticsAccount to your account # provided by Google Analytics. -->";
	} else {
		
		if ( !$skip ) {
			$gaAccount = "_gaq.push(['_setAccount', '".$wgGoogleAnalyticsAccount."']);";

			$code =<<<HTML
<script type="text/javascript">
 var _gaq = _gaq || [];
 var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
 {$gaAccount}
 _gaq.push(['_require', 'inpage_linkid', pluginUrl]);
 {$domainName}
 _gaq.push(['_setAllowLinker', true]);
 _gaq.push(['_setAllowHash', false]);
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
	
	$parser->mOutput->addHeadItem( $code );
	return true;

}


