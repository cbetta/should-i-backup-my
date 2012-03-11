<form action="<?php echo url_for('@save') ?>" method="post" accept-charset="utf-8">

<table border="0" cellspacing="5" cellpadding="5">
  <tr><th>Domain</th><th>Key</th><th>Value</th><th>Options</th></tr>

  <?php foreach ($settings as $setting): ?>
    <tr>
      <td><?php echo $setting->getDomain() ?></td>
      <td><?php echo $setting->getKey() ?></td>
      <td><?php echo $setting->getValue() ?></td>
      <td>
        <?php echo link_to(image_tag('delete.png'), '@delete?id='.$setting->getId()) ?>
      </td>
    </tr>
  <?php endforeach ?>
  <tr>
    <td>
      <input type="text" name="domain" value="" id="domain">
    </td>
    <td>
      <input type="text" name="key" value="" id="key">
    </td>
    <td>
      <input type="text" name="value" value="" id="value">
    </td>
    <td>
    </td>
  </tr>
</table>

  <p><input type="submit" value="Save"></p>
</form>