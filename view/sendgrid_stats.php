<div class="wrap clearfix" id="sendgrid-statistics-page">
  <div class="pull-left sendgrid-statistics-header">
    <div id="icon-sendgrid" class="icon32"><br></div>
    <h2 id="sendgrid-wordpress-statistics-header" class="sendgrid-statistics-header-toggle">SendGrid Wordpress Statistics</h2>
    <h2 id="sendgrid-general-statistics-header" class="sendgrid-statistics-header-toggle" style="display: none;">SendGrid General Statistics</h2>
  </div>
  <div class="pull-right sendgrid-statistics-change-type">
    <select id="sendgrid-statistics-change-type">
      <option value="general">General statistics</option>
      <option value="wordpress" selected="selected">Wordpress statistics</option>
      <?php $categories = explode(',', get_option('sendgrid_categories')); ?>
      <?php if($categories): ?>
      <optgroup label="Categories:">
        <?php foreach ($categories as $cateogry): ?>
        <option value="<?php echo $cateogry; ?>"><?php echo $cateogry; ?></option>
        <?php endforeach; ?>
      </optgroup>
      <?php endif; ?>
    </select>
  </div>
  
  <div id="dashboard-widgets-wrap" class="full-width">
    <div id="dashboard-widgets" class="metabox-holder columns-1">
      <div class="postbox-container">
        <div id="sendgrid_statistics_widget" class="postbox ">
          <h3 class="hndle"><span>SendGrid Statistics</span></h3>
          <div class="inside">
            <?php require plugin_dir_path( __FILE__ ) . '../view/partials/sendgrid_stats_widget.php'; ?>
          </div>
        </div>

        <?php
        require plugin_dir_path( __FILE__ ) . '../view/partials/sendgrid_stats_deliveries.php';
        require plugin_dir_path( __FILE__ ) . '../view/partials/sendgrid_stats_compliance.php';
        require plugin_dir_path( __FILE__ ) . '../view/partials/sendgrid_stats_engagement.php';
        ?>
      </div>
    </div>
  </div>
</div>