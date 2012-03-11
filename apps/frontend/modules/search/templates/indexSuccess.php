<div id="frontpage">
  <form action="<?php echo url_for('@search_target') ?>" method="post" accept-charset="utf-8">
    <?php echo image_tag('db.gif') ?>

    <h2>Should I backup my</h2>
    <input type="text" name="search" value="..." id="search" />
    <input type="submit" name="submit" value="?" id="submit" />
  </form>
</div>

<div id="footer">
  Made by <a href='http://cristianobetta.com'>Cristiano Betta</a> for <a href="http://www.hackday.org/">Yahoo Open Hack Day 2009</a>
</div>
