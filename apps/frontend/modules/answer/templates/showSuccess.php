<div id="answer">
  <div class='title'>Should I backup my
  <form action="<?php echo url_for('@search_target') ?>" method="post" accept-charset="utf-8">
    <input type="text" name="search" value="<?php echo $sf_params->get('target') ?>" id="search" />
  </form>?</div>
  <h2 style='color:<?php echo $color ?>'><?php echo $state ?></h2>
  

  
  <?php 
  if (@$settings['logo']) {
    $image_tag = image_tag($settings['logo']);
    
    if (@$settings['link']) {
      echo link_to($image_tag, $settings['link']);
      ?><span><?php echo link_to("Read more...", $settings['link']); ?></span><?php
    } else {
      echo $image_tag;
    }
        
  } 
  
  ?>
    <hr/>
  <?php
  
  if ($settings['backup_links']) {
     ?>
        <h3>Backup Tools</h3>
          <ul class='left'>
      <?php
    foreach ($settings['backup_links'] as $link) {
        echo "<li>".link_to($link->title, $link->url)."</li>";
    }
      ?>
        </ul>
      <?php
  }
  
  if ($settings['news_links']) {
     ?>
        <h3><?php echo $sf_params->get('target')  ?> News</h3>
          <ul class='left'>
      <?php
    foreach ($settings['news_links'] as $link) {
        echo "<li>".link_to($link->headline, $link->webUrl)."</li>";
    }
      ?>
        </ul>
      <?php
  }
  ?>
</div>
