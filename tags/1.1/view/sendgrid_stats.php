<div class="wrap" id="sendgrid-statistics-page">
  <div id="icon-sendgrid" class="icon32"><br></div>
  <h2>SendGrid Statistics</h2>
  
  <div id="dashboard-widgets-wrap">
    <div id="dashboard-widgets" class="metabox-holder columns-1">
      <div id="postbox-container-1" class="postbox-container">
        <div id="normal-sortables" class="meta-box-sortables">
          
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
</div>