<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Fabric</title>
    <link rel="shortcut icon" href="/favicon.ico">
    <?php echo $this->_getHelper()->Asset->stylesheetLinkTag('application'); ?>
  </head>
  <body>
    <div id="wrapper">
      <?php echo $this->_getHelper()->Asset->imageTag('/images/fabric.png') ?>
      <h2><?php echo _('Fabric - A small, light weight and fast PHP framework for web sites') ?></h2>
      <?php echo $contentForLayout; ?>
      <?php echo $this->_getHelper()->Asset->javascriptIncludeTag('application'); ?>
    </div>
  </body>
</html>