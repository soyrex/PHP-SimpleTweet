<?php
/**
 * SimpleTweet
 *
 * A simple tweet object. Not much else to say here.
 */
date_default_timezone_set('Europe/London');

class SimpleTweet {
  var $cacheDir = '.';
  var $cacheTime = 3600; // 1 hour
  var $user = '';
  var $count = 0;
  function __construct($user,$count=1) {
    $this->user = $user;
    $this->count = $count;
  }

  function get() {
    $filename = "{$this->cacheDir}/.simpletweet.cache.json";
    if(file_exists($filename)) {
      $url = $filename;
    }
    else {
      $url    = "http://search.twitter.com/search.json?q=from:{$this->user}&rpp={$this->count}";
    }
    try {
      $json = file_get_contents($url);
    } catch (Exception $e) {
      // die silently :)
      $json = false;
    }
    $result = false;
    if($json) {
      $result = json_decode($json);

      // check if it needs to be cached:
      if(!isset($result->timestamp) || time()-$result->timestamp > $this->cacheTime) {
        $result->timestamp = time();
        $file = fopen($filename,'w');
        fwrite($file,json_encode($result));
      }
    }

    return($result);
  }



  function getLatestTweet() {
    $tweets = $this->get();
    return ($tweets->results[0]);
  }

  function drawLatestTweet() {
    $tweet = $this->getLatestTweet();
    ?>
    <div class="simpletweet" itemscope itemtype="Tweet">
      <p itemprop="content" class="tweettext"><?php echo($tweet->text) ?></p>
      <a itemprop="user" href="http://twitter.com/<?php echo($tweet->from_user) ?>">@<?php echo($tweet->from_user) ?></a>
      <date><?php echo($this->_ago(strtotime($tweet->created_at))) ?>ago</date>
    </div><?
  }
  function _ago($tm,$rcs = 0) {
     $cur_tm = time(); $dif = $cur_tm-$tm;
     $pds = array('second','minute','hour','day','week','month','year','decade');
     $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
     for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);

     $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
     if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
     return $x;
  }
}


?>
