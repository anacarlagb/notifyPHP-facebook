<?php

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

//RESPONSAVEL POR CONEXAO COM O SERVIDOR
$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));



$user_id = $facebook->getUser();
$app_token = '1412219535660620|zbPCKvR-6RXka3Xd6MG4vEeQJZk';

$notificationdata = array(
    'href'=> 'https://apps.facebook.com/playtestnotify/',
    'access_token'=> $app_token,
    'template'=> '180 char string as information'
);


if ($user_id) {
	  try {
	    // Fetch the viewer's basic information
	    //$basic = $facebook->api('/me');
	    $permissions = $facebook->api("/me/permissions");
	      if(! (array_key_exists('publish_stream', $permissions['data'][0])
		)) {
		    header("Location: " . $facebook->getLoginUrl(array("scope" => "publish_stream")));
		        exit;
		}
              $sendnotification = $facebook->api('/' . $user_id . '/notifications', 'post', $notificationdata);
           
           /*POST /{recipient_userid}/notifications?
           access_token= … & 
           href= … & 
           template=@[596824621] started a game with you, play now!
            */
	  } catch (FacebookApiException $e) {
	    // If the call fails we check if we still have a user. The user will be
	    // cleared if the error is because of an invalid accesstoken

	    error_log($e);
	    $user = null;
	    if (!$facebook->getUser()) {
	      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
	      exit();
	    }
	  }
    echo $user_id;
 

 
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());

$app_name = idx($app_info, 'name', '');

?>


<!DOCTYPE html>
<!-- RESPONSAVEL PELO LAYOUT DA PAGE CANVAS DO APP-->
<!-- APP PEDE PERMISSAO AO USUARIO-->
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />


    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo he($app_name); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="My first app" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script type="text/javascript" src="/javascript/jquery-1.7.1.min.js"></script>

    <script type="text/javascript">
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }
       
      //RESPONSAVEL PELO BOTAO DE POSTAR NO MURAL
      $(function(){
        // Set up so we handle click on the buttons
        $('#postWall').click(function() {
          FB.ui(
            {
             //AQUI QUE COLOCA A MENSAGEM DO MURAL
              method : 'feed',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

      
      });
    </script>

   
  </head>
  <body>
    <div id="fb-root"></div>
    <script type="text/javascript">
      //quando usuario esta conectado no app
      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
        FB.Event.subscribe('auth.login', function(response) {
          // We want to reload the page now so PHP can read the cookie that the
          // Javascript SDK sat. But we don't want to use
          // window.location.reload() because if this is in a canvas there was a
          // post made to this page and a reload will trigger a message to the
          // user asking if they want to send data again.
          window.location = window.location;
        });

        FB.Canvas.setAutoGrow();
      };

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>

    <header class="clearfix">
      <?php if (isset($permissions)) { ?>
      <p id="picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

      <div>
        <h1>Welcome, <strong><?php echo he(idx($permissions, 'name')); ?></strong></h1>
        <p class="tagline">
          This is your app
          <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top"><?php echo he($app_name); ?></a>
        </p> 
        

        <div id="share-app">
          <p>Share your app:</p>
          <ul>
            <li>
              <a href="#" class="facebook-button" id="postWall" data-url="<?php echo AppInfo::getUrl(); ?>">
                <span class="plus">Post Wall</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <?php } else { ?>
      <div>
        <h1>Welcome</h1>
        <div class="fb-login-button" data-scope="user_likes,user_photos, publish_stream"></div>
      </div>
      <?php
         
      } ?>
    </header>        
  </body>
</html>

