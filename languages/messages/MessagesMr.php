<?php
/** Marathi (मराठी)
 *
 * @file
 * @ingroup Languages
 *
 * @author Aan
 * @author Angela
 * @author Ankitgadgil
 * @author Ashupawar
 * @author Balaji
 * @author Chandu
 * @author Dnyanesh325
 * @author Harshalhayat
 * @author Hemanshu
 * @author Hemant wikikosh1
 * @author Htt
 * @author Kaajawa
 * @author Kaganer
 * @author Kaustubh
 * @author Mahitgar
 * @author Marathipremi101
 * @author Mohanpurkar
 * @author Mvkulkarni23
 * @author Prabodh1987
 * @author Pranav jagtap
 * @author Rahuldeshmukh101
 * @author Rdeshmuk
 * @author Sankalpdravid
 * @author Sau6402
 * @author Shantanoo
 * @author Shreewiki
 * @author Shreyas19
 * @author Sudhanwa
 * @author Tusharpawar1982
 * @author V.narsikar
 * @author Vibhavari
 * @author Vpnagarkar
 * @author Ydyashad
 * @author Ynwala
 * @author अभय नातू
 * @author कोलࣿहापࣿरी
 * @author कोल्हापुरी
 * @author प्रणव कुलकर्णी
 * @author प्रतिमा
 * @author शࣿरीहरि
 * @author संतोष दहिवळ
 */

$namespaceNames = [
	NS_MEDIA            => 'मिडिया',
	NS_SPECIAL          => 'विशेष',
	NS_TALK             => 'चर्चा',
	NS_USER             => 'सदस्य',
	NS_USER_TALK        => 'सदस्य_चर्चा',
	NS_PROJECT_TALK     => '$1_चर्चा',
	NS_FILE             => 'चित्र',
	NS_FILE_TALK        => 'चित्र_चर्चा',
	NS_MEDIAWIKI        => 'मिडियाविकी',
	NS_MEDIAWIKI_TALK   => 'मिडियाविकी_चर्चा',
	NS_TEMPLATE         => 'साचा',
	NS_TEMPLATE_TALK    => 'साचा_चर्चा',
	NS_HELP             => 'सहाय्य',
	NS_HELP_TALK        => 'सहाय्य_चर्चा',
	NS_CATEGORY         => 'वर्ग',
	NS_CATEGORY_TALK    => 'वर्ग_चर्चा',
];

$namespaceAliases = [
	'साहाय्य' => NS_HELP,
	'साहाय्य_चर्चा' => NS_HELP_TALK,
];

# !!# sqlविचारा is not in normalised form, which is Sqlविचारा or Sqlविचारा
/** @phpcs-require-sorted-array */
$specialPageAliases = [
	'Activeusers'               => [ 'सक्रिय_सदस्य' ],
	'Allmessages'               => [ 'सर्व_निरोप' ],
	'Allpages'                  => [ 'सर्व_पाने' ],
	'Ancientpages'              => [ 'जुनी_पाने' ],
	'Blankpage'                 => [ 'कोरे_पान' ],
	'Block'                     => [ 'प्रतिबंध', 'अंकपत्ता_प्रतिबंध', 'सदस्य_प्रतिबंध' ],
	'BlockList'                 => [ 'प्रतिबंधन_सुची' ],
	'Booksources'               => [ 'पुस्तक_स्रोत' ],
	'BrokenRedirects'           => [ 'चुकीची_पुनर्निर्देशने' ],
	'Categories'                => [ 'वर्ग' ],
	'ChangeEmail'               => [ 'विपत्र_बदला' ],
	'ChangePassword'            => [ 'परवलीचा_शब्द_बदला' ],
	'ComparePages'              => [ 'पानांची_तुलना' ],
	'Confirmemail'              => [ 'विपत्र_नक्की_करा' ],
	'Contributions'             => [ 'योगदान' ],
	'CreateAccount'             => [ 'सदस्य_नोंद' ],
	'Deadendpages'              => [ 'टोकाची_पाने' ],
	'DeletedContributions'      => [ 'वगळलेली_योगदाने' ],
	'DoubleRedirects'           => [ 'दुहेरी_पुनर्निर्देशने' ],
	'Emailuser'                 => [ 'विपत्र_वापरकर्ता' ],
	'ExpandTemplates'           => [ 'साचेविस्तारकरा' ],
	'Export'                    => [ 'निर्यात' ],
	'Fewestrevisions'           => [ 'कमीत_कमी_आवर्तने' ],
	'FileDuplicateSearch'       => [ 'दुहेरी_संचिका_शोध' ],
	'Filepath'                  => [ 'संचिकेचा_पत्ता_(पाथ)' ],
	'Import'                    => [ 'आयात' ],
	'Interwiki'                 => [ 'आंतरविकि' ],
	'Invalidateemail'           => [ 'अग्राह्य_विपत्र' ],
	'LinkSearch'                => [ 'दुवाशोध' ],
	'Listadmins'                => [ 'प्रबंधकांची_यादी' ],
	'Listbots'                  => [ 'सांगकाम्यांची_यादी' ],
	'Listfiles'                 => [ 'चित्रयादी' ],
	'Listgrouprights'           => [ 'गट_अधिकार_यादी' ],
	'Listredirects'             => [ 'पुर्ननिर्देशन_सुची' ],
	'Listusers'                 => [ 'सदस्यांची_यादी' ],
	'Lockdb'                    => [ 'डेटाबेस_कुलुपबंद_करा' ],
	'Log'                       => [ 'नोंद', 'नोंदी' ],
	'Lonelypages'               => [ 'एकाकी_पाने' ],
	'Longpages'                 => [ 'मोठी_पाने' ],
	'MergeHistory'              => [ 'इतिहास_एकत्र_करा' ],
	'MIMEsearch'                => [ 'माईम‌_शोध' ],
	'Mostcategories'            => [ 'सर्वात_जास्त_वर्ग' ],
	'Mostimages'                => [ 'सर्वाधिक_सांधलेली_संचिका' ],
	'Mostlinked'                => [ 'सर्वात_जास्त_जोडलेली' ],
	'Mostlinkedcategories'      => [ 'सर्वात_जास्त_जोडलेले_वर्ग', 'सर्वात_जास्त_वापरलेले_वर्ग' ],
	'Mostlinkedtemplates'       => [ 'सर्वात_जास्त_जोडलेले_साचे', 'सर्वात_जास्त_वापरलेले_साचे' ],
	'Mostrevisions'             => [ 'सर्वाधिकआवर्तने' ],
	'Movepage'                  => [ 'पान_हलवा' ],
	'Mycontributions'           => [ 'माझे_योगदान' ],
	'MyLanguage'                => [ 'माझीभाषा' ],
	'Mypage'                    => [ 'माझे_पान' ],
	'Mytalk'                    => [ 'माझ्या_चर्चा' ],
	'Newimages'                 => [ 'नवीन_संचिका', 'नवीन_चित्रे' ],
	'Newpages'                  => [ 'नवीन_पाने' ],
	'Preferences'               => [ 'पसंती' ],
	'Prefixindex'               => [ 'उपसर्गसुची' ],
	'Protectedpages'            => [ 'सुरक्षित_पाने' ],
	'Protectedtitles'           => [ 'सुरक्षित_शीर्षके' ],
	'Randompage'                => [ 'कोणतेही', 'कोणतेही_पृष्ठ' ],
	'Randomredirect'            => [ 'अविशिष्ट_पुर्ननिर्देशन' ],
	'Recentchanges'             => [ 'अलीकडील_बदल' ],
	'Recentchangeslinked'       => [ 'सांधलेलेअलिकडीलबदल' ],
	'Renameuser'                => [ 'सदस्यपुर्नामितकरा' ],
	'Revisiondelete'            => [ 'आवर्तनवगळा' ],
	'Search'                    => [ 'शोधा' ],
	'Shortpages'                => [ 'छोटी_पाने' ],
	'Specialpages'              => [ 'विशेष_पाने' ],
	'Statistics'                => [ 'सांख्यिकी' ],
	'Tags'                      => [ 'खूणा' ],
	'Uncategorizedcategories'   => [ 'अवर्गीकृत_वर्ग' ],
	'Uncategorizedimages'       => [ 'अवर्गीकृत_संचिका', 'अवर्गीकृत_चित्रे' ],
	'Uncategorizedpages'        => [ 'अवर्गीकृत_पाने' ],
	'Uncategorizedtemplates'    => [ 'अवर्गीकृत_साचे' ],
	'Undelete'                  => [ 'काढणे_रद्द_करा' ],
	'Unlockdb'                  => [ 'विदागारताळेउघडा' ],
	'Unusedcategories'          => [ 'न_वापरलेले_वर्ग' ],
	'Unusedimages'              => [ 'न_वापरलेली_चित्रे' ],
	'Unusedtemplates'           => [ 'उपयोगात_नसलेले_साचे' ],
	'Unwatchedpages'            => [ 'अप्रेक्षीत_पाने' ],
	'Upload'                    => [ 'चढवा' ],
	'Userlogin'                 => [ 'सदस्य_प्रवेश' ],
	'Userlogout'                => [ 'सदस्य‌_बहिर्गमन' ],
	'Userrights'                => [ 'खातेदाराचे_अधिकार' ],
	'Version'                   => [ 'आवृत्ती' ],
	'Wantedcategories'          => [ 'हवे_असलेले_वर्ग' ],
	'Wantedfiles'               => [ 'संचिका_हवी' ],
	'Wantedpages'               => [ 'हवे_असलेले_लेख' ],
	'Wantedtemplates'           => [ 'साचा_हवा' ],
	'Watchlist'                 => [ 'नित्य‌_पहाण्याची_सूची' ],
	'Whatlinkshere'             => [ 'येथे_काय_जोडले_आहे' ],
	'Withoutinterwiki'          => [ 'आंतरविकि_शिवाय' ],
];

/** @phpcs-require-sorted-array */
$magicWords = [
	'anchorencode'              => [ '0', 'नांगरआंग्लसंकेत', 'ANCHORENCODE' ],
	'basepagename'              => [ '1', 'मूळपाननाव', 'BASEPAGENAME' ],
	'basepagenamee'             => [ '1', 'मूळपाननावे', 'BASEPAGENAMEE' ],
	'contentlanguage'           => [ '1', 'मसुदाभाषा', 'मजकुरभाषा', 'CONTENTLANGUAGE', 'CONTENTLANG' ],
	'currentday'                => [ '1', 'सद्यदिवस', 'CURRENTDAY' ],
	'currentday2'               => [ '1', 'सद्यदिवस२', 'CURRENTDAY2' ],
	'currentdayname'            => [ '1', 'सद्यदिवसनाव', 'CURRENTDAYNAME' ],
	'currentdow'                => [ '1', 'सद्यउतरण', 'सद्यउतार', 'CURRENTDOW' ],
	'currenthour'               => [ '1', 'सद्यतास', 'CURRENTHOUR' ],
	'currentmonth'              => [ '1', 'सद्यमहिना', 'सद्यमहिना२', 'CURRENTMONTH', 'CURRENTMONTH2' ],
	'currentmonth1'             => [ '1', 'सद्यमहिना१', 'CURRENTMONTH1' ],
	'currentmonthabbrev'        => [ '1', 'सद्यमहिनासंक्षीप्त', 'CURRENTMONTHABBREV' ],
	'currentmonthname'          => [ '1', 'सद्यमहिनानाव', 'CURRENTMONTHNAME' ],
	'currentmonthnamegen'       => [ '1', 'सद्यमहिनासाधारण', 'CURRENTMONTHNAMEGEN' ],
	'currenttime'               => [ '1', 'सद्यवेळ', 'CURRENTTIME' ],
	'currenttimestamp'          => [ '1', 'सद्यकालमुद्रा', 'CURRENTTIMESTAMP' ],
	'currentversion'            => [ '1', 'सद्यआवृत्ती', 'CURRENTVERSION' ],
	'currentweek'               => [ '1', 'सद्यआठवडा', 'CURRENTWEEK' ],
	'currentyear'               => [ '1', 'सद्यवर्ष', 'CURRENTYEAR' ],
	'defaultsort'               => [ '1', 'अविचलवर्ग:', 'अविचलवर्गकळ:', 'अविचलवर्गवर्गीकरण:', 'DEFAULTSORT:', 'DEFAULTSORTKEY:', 'DEFAULTCATEGORYSORT:' ],
	'directionmark'             => [ '1', 'दिशाचिन्ह', 'दिशादर्शक', 'DIRECTIONMARK', 'DIRMARK' ],
	'displaytitle'              => [ '1', 'शीर्षकदाखवा', 'DISPLAYTITLE' ],
	'filepath'                  => [ '0', 'संचिकामार्ग:', 'FILEPATH:' ],
	'forcetoc'                  => [ '0', '__अनुक्रमणिकाहवीच__', '__FORCETOC__' ],
	'formatdate'                => [ '0', 'दिनांकनपद्धती', 'formatdate', 'dateformat' ],
	'formatnum'                 => [ '0', 'क्रमपद्धती', 'FORMATNUM' ],
	'fullpagename'              => [ '1', 'पूर्णलेखनाव', 'FULLPAGENAME' ],
	'fullpagenamee'             => [ '1', 'पूर्णलेखनावे', 'अंशदुवा', 'FULLPAGENAMEE' ],
	'fullurl'                   => [ '0', 'संपूर्णसंस्थळ', 'FULLURL:' ],
	'fullurle'                  => [ '0', 'संपूर्णसंस्थली:', 'संपूर्णसंस्थळी:', 'FULLURLE:' ],
	'gender'                    => [ '0', 'लिंग:', 'GENDER:' ],
	'grammar'                   => [ '0', 'व्याकरण:', 'GRAMMAR:' ],
	'hiddencat'                 => [ '1', '__वर्गलपवा__', '__HIDDENCAT__' ],
	'img_alt'                   => [ '1', 'अल्ट=$1', 'alt=$1' ],
	'img_baseline'              => [ '1', 'तळरेषा', 'आधाररेषा', 'baseline' ],
	'img_border'                => [ '1', 'सीमा', 'border' ],
	'img_bottom'                => [ '1', 'तळ', 'बूड', 'bottom' ],
	'img_center'                => [ '1', 'मध्यवर्ती', 'center', 'centre' ],
	'img_framed'                => [ '1', 'चौकट', 'फ़्रेम', 'frame', 'framed', 'enframed' ],
	'img_frameless'             => [ '1', 'विनाचौकट', 'विनाफ़्रेम', 'frameless' ],
	'img_left'                  => [ '1', 'डावे', 'left' ],
	'img_link'                  => [ '1', 'दुवा=$1', 'link=$1' ],
	'img_manualthumb'           => [ '1', 'इवलेसे=$1', 'thumbnail=$1', 'thumb=$1' ],
	'img_middle'                => [ '1', 'मध्य', 'middle' ],
	'img_none'                  => [ '1', 'कोणतेचनाही', 'नन्ना', 'none' ],
	'img_page'                  => [ '1', 'पान=$1', 'पान_$1', 'page=$1', 'page $1' ],
	'img_right'                 => [ '1', 'उजवे', 'right' ],
	'img_sub'                   => [ '1', 'अधो', 'sub' ],
	'img_super'                 => [ '1', 'उर्ध्व', 'super', 'sup' ],
	'img_text_bottom'           => [ '1', 'मजकुरतळ', 'text-bottom' ],
	'img_text_top'              => [ '1', 'मजकूर-शीर्ष', 'शीर्ष-मजकूर', 'text-top' ],
	'img_thumbnail'             => [ '1', 'इवलेसे', 'thumb', 'thumbnail' ],
	'img_top'                   => [ '1', 'अत्यूच्च', 'top' ],
	'img_upright'               => [ '1', 'उभा', 'उभा=$1', 'उभा_$1', 'upright', 'upright=$1', 'upright $1' ],
	'img_width'                 => [ '1', '$1अंश', '$1कणी', '$1पक्ष', '$1px' ],
	'index'                     => [ '1', '__क्रमीत__', '__अनुक्रमीत__', '__INDEX__' ],
	'int'                       => [ '0', 'इन्ट:', 'INT:' ],
	'language'                  => [ '0', '#भाषा', '#LANGUAGE' ],
	'localday'                  => [ '1', 'स्थानिकदिवस', 'LOCALDAY' ],
	'localday2'                 => [ '1', 'स्थानिकदिवस२', 'LOCALDAY2' ],
	'localdayname'              => [ '1', 'स्थानिकदिवसनाव', 'LOCALDAYNAME' ],
	'localdow'                  => [ '1', 'स्थानिकउतरण', 'स्थानिकउतार', 'LOCALDOW' ],
	'localhour'                 => [ '1', 'स्थानिकतास', 'LOCALHOUR' ],
	'localmonth'                => [ '1', 'स्थानिकमहिना', 'स्थानिकमहिना२', 'LOCALMONTH', 'LOCALMONTH2' ],
	'localmonth1'               => [ '1', 'स्थानिकमहिना१', 'LOCALMONTH1' ],
	'localmonthabbrev'          => [ '1', 'स्थानिकमहिनासंक्षीप्त', 'LOCALMONTHABBREV' ],
	'localmonthname'            => [ '1', 'स्थानिकमहिनानाव', 'LOCALMONTHNAME' ],
	'localmonthnamegen'         => [ '1', 'स्थानिकमहिनासाधारण', 'LOCALMONTHNAMEGEN' ],
	'localtime'                 => [ '1', 'स्थानिकवेळ', 'LOCALTIME' ],
	'localtimestamp'            => [ '1', 'स्थानिककालमुद्रा', 'LOCALTIMESTAMP' ],
	'localurl'                  => [ '0', 'स्थानिकस्थळ:', 'स्थानिकसंकेतस्थळ:', 'LOCALURL:' ],
	'localurle'                 => [ '0', 'स्थानिकस्थली:', 'LOCALURLE:' ],
	'localweek'                 => [ '1', 'स्थानिकआठवडा', 'LOCALWEEK' ],
	'localyear'                 => [ '1', 'स्थानिकवर्ष', 'LOCALYEAR' ],
	'msg'                       => [ '0', 'संदेश:', 'निरोप:', 'MSG:' ],
	'msgnw'                     => [ '0', 'संदेशनवा:', 'निरोपनवा:', 'MSGNW:' ],
	'namespace'                 => [ '1', 'नामविश्व', 'NAMESPACE' ],
	'namespacee'                => [ '1', 'नामविश्वा', 'नामविश्वाचे', 'NAMESPACEE' ],
	'namespacenumber'           => [ '1', 'नामविश्वक्रमांक', 'NAMESPACENUMBER' ],
	'newsectionlink'            => [ '1', '__नवविभागदुवा__', '__NEWSECTIONLINK__' ],
	'nocontentconvert'          => [ '0', '__विनामजकुरबदल__', '__विनामब__', '__NOCONTENTCONVERT__', '__NOCC__' ],
	'noeditsection'             => [ '0', '__विभागअसंपादनक्षम__', '__NOEDITSECTION__' ],
	'nogallery'                 => [ '0', '__प्रदर्शननको__', '__NOGALLERY__' ],
	'noindex'                   => [ '1', '__विनाक्रमीत__', '__विनाअनुक्रमीत__', '__NOINDEX__' ],
	'nonewsectionlink'          => [ '1', '__विनानवविभागदुवा__', '__NONEWSECTIONLINK__' ],
	'notitleconvert'            => [ '0', '__विनाशीर्षकबदल__', '__विनाशीब__', '__NOTITLECONVERT__', '__NOTC__' ],
	'notoc'                     => [ '0', '__अनुक्रमणिकानको__', '__NOTOC__' ],
	'ns'                        => [ '0', 'नावि:', 'NS:' ],
	'nse'                       => [ '0', 'नाविअरिक्त:', 'नाव्यारिक्त:', 'नाव्याख:', 'NSE:' ],
	'numberingroup'             => [ '1', 'गटक्रमांक', 'NUMBERINGROUP', 'NUMINGROUP' ],
	'numberofactiveusers'       => [ '1', 'सक्रीयसदस्यसंख्या', 'NUMBEROFACTIVEUSERS' ],
	'numberofadmins'            => [ '1', 'प्रचालकसंख्या', 'NUMBEROFADMINS' ],
	'numberofarticles'          => [ '1', 'लेखसंख्या', 'NUMBEROFARTICLES' ],
	'numberofedits'             => [ '1', 'संपादनसंख्या', 'NUMBEROFEDITS' ],
	'numberoffiles'             => [ '1', 'संचिकासंख्या', 'NUMBEROFFILES' ],
	'numberofpages'             => [ '1', 'पानसंख्या', 'NUMBEROFPAGES' ],
	'numberofusers'             => [ '1', 'सदस्यसंख्या', 'NUMBEROFUSERS' ],
	'padleft'                   => [ '0', 'डावाभरीव', 'भरीवडावा', 'PADLEFT' ],
	'padright'                  => [ '0', 'उजवाभरीव', 'भरीवउजवा', 'PADRIGHT' ],
	'pagename'                  => [ '1', 'लेखनाव', 'PAGENAME' ],
	'pagenamee'                 => [ '1', 'लेखानावव', 'PAGENAMEE' ],
	'pagesincategory'           => [ '1', 'वर्गातीलपाने', 'वर्गीतपाने', 'श्रेणीतपाने', 'PAGESINCATEGORY', 'PAGESINCAT' ],
	'pagesincategory_all'       => [ '0', 'सर्व', 'all' ],
	'pagesincategory_files'     => [ '0', 'संचिका', 'files' ],
	'pagesincategory_pages'     => [ '0', 'पाने', 'pages' ],
	'pagesincategory_subcats'   => [ '0', 'उपवर्ग', 'subcats' ],
	'pagesinnamespace'          => [ '1', 'नामविश्वातीलपाने:', 'PAGESINNAMESPACE:', 'PAGESINNS:' ],
	'pagesize'                  => [ '1', 'पानक्षमता', 'PAGESIZE' ],
	'plural'                    => [ '0', 'बहुवचन:', 'PLURAL:' ],
	'protectionlevel'           => [ '1', 'सुरक्षास्तर', 'PROTECTIONLEVEL' ],
	'raw'                       => [ '0', 'कच्चे:', 'RAW:' ],
	'rawsuffix'                 => [ '1', 'ॠ', 'R' ],
	'redirect'                  => [ '0', '#पुनर्निर्देशन', '#पुर्ननिर्देशन', '#REDIRECT' ],
	'revisionday'               => [ '1', 'आवृत्तीदिन', 'REVISIONDAY' ],
	'revisionday2'              => [ '1', 'आवृत्तीदिन२', 'REVISIONDAY2' ],
	'revisionid'                => [ '1', 'आवृत्तीक्र्मांक', 'REVISIONID' ],
	'revisionmonth'             => [ '1', 'आवृत्तीमास', 'REVISIONMONTH' ],
	'revisiontimestamp'         => [ '1', 'आवृत्तीमुद्रा', 'आवृत्तीठसा', 'REVISIONTIMESTAMP' ],
	'revisionuser'              => [ '1', 'आवृत्तीसदस्य', 'REVISIONUSER' ],
	'revisionyear'              => [ '1', 'आवृत्तीवर्ष', 'REVISIONYEAR' ],
	'scriptpath'                => [ '0', 'संहीतामार्ग', 'SCRIPTPATH' ],
	'server'                    => [ '0', 'विदादाता', 'SERVER' ],
	'servername'                => [ '0', 'विदादातानाव', 'SERVERNAME' ],
	'sitename'                  => [ '1', 'संकेतस्थळनाव', 'SITENAME' ],
	'special'                   => [ '0', 'विशेष', 'special' ],
	'staticredirect'            => [ '1', '__अविचलपुर्ननिर्देश__', '__STATICREDIRECT__' ],
	'subjectpagename'           => [ '1', 'विषयपाननाव', 'लेखपाननाव', 'SUBJECTPAGENAME', 'ARTICLEPAGENAME' ],
	'subjectpagenamee'          => [ '1', 'विषयपाननावे', 'लेखपाननावे', 'SUBJECTPAGENAMEE', 'ARTICLEPAGENAMEE' ],
	'subjectspace'              => [ '1', 'विषयविश्व', 'लेखविश्व', 'SUBJECTSPACE', 'ARTICLESPACE' ],
	'subjectspacee'             => [ '1', 'विषयविश्वा', 'लेखविश्वा', 'विषयविश्वाचे', 'लेखविश्वाचे', 'SUBJECTSPACEE', 'ARTICLESPACEE' ],
	'subpagename'               => [ '1', 'उपपाननाव', 'SUBPAGENAME' ],
	'subpagenamee'              => [ '1', 'उपपाननावे', 'उपपाननावाचे', 'उपौंशदुवा', 'SUBPAGENAMEE' ],
	'subst'                     => [ '0', 'पर्याय:', 'समाविष्टी:', 'अबाह्य:', 'निरकंसबिंब:', 'कंसत्याग:', 'साचाहिन:', 'साचान्तर:', 'साचापरिस्फोट:', 'साचोद्घाटन:', 'SUBST:' ],
	'tag'                       => [ '0', 'खूण', 'खूणगाठ', 'tag' ],
	'talkpagename'              => [ '1', 'चर्चापाननाव', 'TALKPAGENAME' ],
	'talkpagenamee'             => [ '1', 'चर्चापाननावे', 'TALKPAGENAMEE' ],
	'talkspace'                 => [ '1', 'चर्चाविश्व', 'TALKSPACE' ],
	'talkspacee'                => [ '1', 'चर्चाविश्वा', 'चर्चाविश्वाचे', 'TALKSPACEE' ],
	'toc'                       => [ '0', '__अनुक्रमणिका__', '__TOC__' ],
	'urlencode'                 => [ '0', 'संकेतस्थलीआंग्ल्संकेत:', 'URLENCODE:' ],
	'url_wiki'                  => [ '0', 'विकि', 'WIKI' ],
];

$digitTransformTable = [
	'0' => '०', # U+0966
	'1' => '१', # U+0967
	'2' => '२', # U+0968
	'3' => '३', # U+0969
	'4' => '४', # U+096A
	'5' => '५', # U+096B
	'6' => '६', # U+096C
	'7' => '७', # U+096D
	'8' => '८', # U+096E
	'9' => '९', # U+096F
];

$numberingSystem = 'deva';

$linkTrail = "/^([\u{0900}-\u{0963}\u{0971}-\u{097F}\u{FEFF}\u{200D}]+)(.*)$/sDu";

$digitGroupingPattern = "#,##,##0.###";
