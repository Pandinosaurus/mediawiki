<?php
/** Cornish (kernowek)
 *
 * @file
 * @ingroup Languages
 *
 * @author Kernoweger
 * @author Kw-Moon
 * @author MF-Warburg
 * @author Malafaya
 * @author Mongvras
 * @author Nemo bis
 * @author Nicky.ker
 * @author Nrowe
 * @author Scryfer
 */

$namespaceNames = [
	NS_MEDIA            => 'Media',
	NS_SPECIAL          => 'Arbennek',
	NS_TALK             => 'Keskows',
	NS_USER             => 'Devnydhyer',
	NS_USER_TALK        => 'Keskows_Devnydhyer',
	NS_PROJECT_TALK     => 'Keskows_$1',
	NS_FILE             => 'Restren',
	NS_FILE_TALK        => 'Keskows_Restren',
	NS_MEDIAWIKI        => 'MediaWiki',
	NS_MEDIAWIKI_TALK   => 'Keskows_MediaWiki',
	NS_TEMPLATE         => 'Skantlyn',
	NS_TEMPLATE_TALK    => 'Keskows_Skantlyn',
	NS_HELP             => 'Gweres',
	NS_HELP_TALK        => 'Keskows_Gweres',
	NS_CATEGORY         => 'Klass',
	NS_CATEGORY_TALK    => 'Keskows_Klass',
];

$namespaceAliases = [
	'Arbednek'           => NS_SPECIAL,
	'Cows'               => NS_TALK,
	'Kescows'            => NS_TALK,
	'Cows_Devnydhyer'    => NS_USER_TALK,
	'Kescows_Devnydhyer' => NS_USER_TALK,
	'Cows_$1'            => NS_PROJECT_TALK,
	'Kescows_$1'         => NS_PROJECT_TALK,
	'Cows_Restren'       => NS_FILE_TALK,
	'Kescows_Restren'    => NS_FILE_TALK,
	'Cows_MediaWiki'     => NS_MEDIAWIKI_TALK,
	'Kescows_MediaWiki'  => NS_MEDIAWIKI_TALK,
	'Cows_Scantlyn'      => NS_TEMPLATE_TALK,
	'Scantlyn'           => NS_TEMPLATE,
	'Kescows_Skantlyn'   => NS_TEMPLATE_TALK,
	'Cows_Gweres'        => NS_HELP_TALK,
	'Kescows_Gweres'     => NS_HELP_TALK,
	'Cows_Class'         => NS_CATEGORY_TALK,
	'Class'              => NS_CATEGORY,
	'Kescows_Class'      => NS_CATEGORY_TALK,
];

/** @phpcs-require-sorted-array */
$specialPageAliases = [
	'Activeusers'               => [ 'DevnydhyoryonVyw' ],
	'Allmessages'               => [ 'OllMessajys' ],
	'Allpages'                  => [ 'OllFolennow' ],
	'Ancientpages'              => [ 'FolennowKoth' ],
	'Badtitle'                  => [ 'TitelDrog' ],
	'Blankpage'                 => [ 'FolenWag' ],
	'Block'                     => [ 'Difenna' ],
	'BlockList'                 => [ 'RolDhifen' ],
	'Booksources'               => [ 'PennfentynyowLyver' ],
	'BrokenRedirects'           => [ 'DaskedyansowTerrys' ],
	'Categories'                => [ 'Klassys' ],
	'ChangeEmail'               => [ 'ChanjyaEbost' ],
	'ChangePassword'            => [ 'ChanjyaGerTremena' ],
	'ComparePages'              => [ 'KeheveliFolennow' ],
	'Confirmemail'              => [ 'AfydhyaEbost' ],
	'Contributions'             => [ 'Kevrohow' ],
	'CreateAccount'             => [ 'FormyaAkont' ],
	'Deadendpages'              => [ 'FolennowFordhDhall' ],
	'DeletedContributions'      => [ 'KevrohowDiles' ],
	'DoubleRedirects'           => [ 'DaswedyansowDewblek' ],
	'EditWatchlist'             => [ 'ChanjyaOwRolWolya' ],
	'Emailuser'                 => [ 'EbostyaDevnydhyer' ],
	'ExpandTemplates'           => [ 'BrasheSkantlyns' ],
	'Export'                    => [ 'Esperthi' ],
	'Fewestrevisions'           => [ 'AnLyhaAmendyansow' ],
	'Filepath'                  => [ 'HynsAnFolen' ],
	'Import'                    => [ 'Ymperthi' ],
	'Interwiki'                 => [ 'Yntrawiki' ],
	'Invalidateemail'           => [ 'DigomposaEbost' ],
	'JavaScriptTest'            => [ 'PrevyansJavaScript' ],
	'LinkSearch'                => [ 'HwilasKevrennow' ],
	'Listadmins'                => [ 'RolyaMenystroryon' ],
	'Listbots'                  => [ 'RolyaBottys' ],
	'Listfiles'                 => [ 'RolyaRestrennow' ],
	'Listgrouprights'           => [ 'RolyaGwiryowBagas' ],
	'Listredirects'             => [ 'RolyaDaskedyansow' ],
	'Listusers'                 => [ 'RolyaDevnydhyoryon' ],
	'Lockdb'                    => [ 'AlhwedhaDB' ],
	'Log'                       => [ 'Kovnoten', 'Kovnotennow' ],
	'Lonelypages'               => [ 'FolennowDigoweth' ],
	'Longpages'                 => [ 'FolennowHir' ],
	'MergeHistory'              => [ 'IstoriKesunya' ],
	'MIMEsearch'                => [ 'HwilasMIME' ],
	'Mostcategories'            => [ 'AnMoyhaKlassys' ],
	'Mostimages'                => [ 'AnMoyhaRestrennowKevennys' ],
	'Mostinterwikis'            => [ 'AnMoyhaInterwikis' ],
	'Mostlinked'                => [ 'AnMoyhaFolennowKevrennys' ],
	'Mostlinkedcategories'      => [ 'AnMoyhaKlassysKevrennys' ],
	'Mostlinkedtemplates'       => [ 'AnMoyhaSkantlynsKevrennys' ],
	'Mostrevisions'             => [ 'AnMoyhaAmendyansow' ],
	'Movepage'                  => [ 'GwayaFolen' ],
	'Mycontributions'           => [ 'OwHevrohow' ],
	'MyLanguage'                => [ 'OwYeth' ],
	'Mypage'                    => [ 'OwFolen' ],
	'Mytalk'                    => [ 'OwHeskows' ],
	'Myuploads'                 => [ 'OwUghkargansow' ],
	'Newimages'                 => [ 'RestrennowNowyth' ],
	'Newpages'                  => [ 'FolennowNowyth' ],
	'PasswordReset'             => [ 'DassetyaGerTremena' ],
	'PermanentLink'             => [ 'KevrenFast' ],
	'Preferences'               => [ 'Dewisyansow' ],
	'Prefixindex'               => [ 'MenegvaRagerow' ],
	'Protectedpages'            => [ 'FolennowDifresys' ],
	'Protectedtitles'           => [ 'TitlysDifres' ],
	'Randompage'                => [ 'FolenDreJons' ],
	'Randomredirect'            => [ 'DaskedyansDreJons' ],
	'Recentchanges'             => [ 'Chanjyow_a-dhiwedhes' ],
	'Recentchangeslinked'       => [ 'ChanjyowKelmys' ],
	'Renameuser'                => [ 'DashenwelDevnydhyer' ],
	'Revisiondelete'            => [ 'DileaAmendyans' ],
	'Search'                    => [ 'Hwilas' ],
	'Shortpages'                => [ 'FolennowBerr' ],
	'Specialpages'              => [ 'FolennowArbennek' ],
	'Statistics'                => [ 'Statystygyon' ],
	'Unblock'                   => [ 'DiswulDifennans' ],
	'Uncategorizedcategories'   => [ 'KlassysHebKlass' ],
	'Uncategorizedimages'       => [ 'RestrennowHebKlass' ],
	'Uncategorizedpages'        => [ 'FolennowHebKlass' ],
	'Uncategorizedtemplates'    => [ 'SkantlynsHebKlass' ],
	'Undelete'                  => [ 'DiswulDilea' ],
	'Unlockdb'                  => [ 'DialhwedhaDB' ],
	'Unusedcategories'          => [ 'KlassysHebDevnydh' ],
	'Unusedimages'              => [ 'RestrennowHebDevnydh' ],
	'Unusedtemplates'           => [ 'SkantlynsHebDevnydh' ],
	'Unwatchedpages'            => [ 'FolennowHebAgaHolya' ],
	'Upload'                    => [ 'Ughkarga' ],
	'Userlogin'                 => [ 'Omgelmi' ],
	'Userlogout'                => [ 'Digelmi' ],
	'Userrights'                => [ 'GwiryowDevnydhyer' ],
	'Version'                   => [ 'Versyon' ],
	'Wantedcategories'          => [ 'KlassysHwansus' ],
	'Wantedfiles'               => [ 'RestrennowHwansus' ],
	'Wantedpages'               => [ 'FolennowHwansus' ],
	'Wantedtemplates'           => [ 'SkantlynsHwansus' ],
	'Watchlist'                 => [ 'Rol_wolya' ],
	'Whatlinkshere'             => [ 'OwKevrennaOmma' ],
	'Withoutinterwiki'          => [ 'HebInterwiki' ],
];

/** @phpcs-require-sorted-array */
$magicWords = [
	'displaytitle'              => [ '1', 'DISKWEDHESANTITEL', 'DISPLAYTITLE' ],
	'filepath'                  => [ '0', 'HYNSANFOLEN:', 'FILEPATH:' ],
	'fullpagename'              => [ '1', 'HANOWLEUNANFOLEN', 'FULLPAGENAME' ],
	'fullurl'                   => [ '0', 'URLLEUN:', 'FULLURL:' ],
	'grammar'                   => [ '0', 'GRAMASEK:', 'GRAMMAR:' ],
	'hiddencat'                 => [ '1', '__KLASSKUDHYS__', '__HIDDENCAT__' ],
	'img_bottom'                => [ '1', 'goles', 'bottom' ],
	'img_center'                => [ '1', 'kresel', 'center', 'centre' ],
	'img_framed'                => [ '1', 'fremys', 'frame', 'framed', 'enframed' ],
	'img_frameless'             => [ '1', 'hebfram', 'frameless' ],
	'img_left'                  => [ '1', 'kledh', 'left' ],
	'img_link'                  => [ '1', 'kevren=$1', 'link=$1' ],
	'img_manualthumb'           => [ '1', 'skeusennik=$1', 'thumbnail=$1', 'thumb=$1' ],
	'img_middle'                => [ '1', 'kres', 'middle' ],
	'img_none'                  => [ '1', 'nagonan', 'none' ],
	'img_page'                  => [ '1', 'folen=$1', 'folen_$1', 'page=$1', 'page $1' ],
	'img_right'                 => [ '1', 'dyhow', 'right' ],
	'img_text_bottom'           => [ '1', 'tekst-goles', 'text-bottom' ],
	'img_text_top'              => [ '1', 'tekst-gwartha', 'text-top' ],
	'img_thumbnail'             => [ '1', 'skeusennik', 'thumb', 'thumbnail' ],
	'img_top'                   => [ '1', 'gwartha', 'top' ],
	'index'                     => [ '1', '__MENEGVA__', '__INDEX__' ],
	'language'                  => [ '0', '#YETH', '#LANGUAGE' ],
	'noindex'                   => [ '1', '__HEBMENEGVA__', '__NOINDEX__' ],
	'numberingroup'             => [ '1', 'NIVERYNBAGAS', 'NUMBERINGROUP', 'NUMINGROUP' ],
	'numberofactiveusers'       => [ '1', 'NIVERADHEVNYDHYORYONVYW', 'NUMBEROFACTIVEUSERS' ],
	'numberofadmins'            => [ '1', 'NIVERAVENYSTRORYON', 'NUMBEROFADMINS' ],
	'numberofarticles'          => [ '1', 'NIVERAERTHYGLOW', 'NUMBEROFARTICLES' ],
	'numberofedits'             => [ '1', 'NIVERAJANJYOW', 'NUMBEROFEDITS' ],
	'numberoffiles'             => [ '1', 'NIVERARESTRENNOW', 'NUMBEROFFILES' ],
	'numberofpages'             => [ '1', 'NIVERAFOLENNOW', 'NUMBEROFPAGES' ],
	'numberofusers'             => [ '1', 'NIVERADHEVNYDHYORYON', 'NUMBEROFUSERS' ],
	'pageid'                    => [ '0', 'IDANFOLEN', 'PAGEID' ],
	'pagename'                  => [ '1', 'HANOWANFOLEN', 'PAGENAME' ],
	'pagesincategory'           => [ '1', 'RESTRENNOWYNKLASS', 'PAGESINCATEGORY', 'PAGESINCAT' ],
	'pagesincategory_all'       => [ '0', 'oll', 'all' ],
	'pagesincategory_pages'     => [ '0', 'folennow', 'pages' ],
	'pagesize'                  => [ '1', 'MYNSANRESTREN', 'PAGESIZE' ],
	'redirect'                  => [ '0', '#DASKEDYANS', '#REDIRECT' ],
	'server'                    => [ '0', 'SERVYER', 'SERVER' ],
	'servername'                => [ '0', 'HANOWANSERVYER', 'SERVERNAME' ],
	'sitename'                  => [ '1', 'HANOWANWIASVA', 'SITENAME' ],
	'special'                   => [ '0', 'arbennek', 'special' ],
	'url_path'                  => [ '0', 'HYNS', 'PATH' ],
];
