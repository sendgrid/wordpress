<div class="sendgrid-filters-container">
  <label for="sendgrid-start-date">Start date</label><input type="text" id="sendgrid-start-date" name="sendgrid-start-date" />
  <label for="sendgrid-end-date">End date</label><input type="text" id="sendgrid-end-date" name="sendgrid-end-date" />
  <a href="#" id="sendgrid-apply-filter" class="button">Apply</a>
</div>
<br style="clear:both;"/>
<div id="sendgrid-container" style="position:relative;">
  <img src="<?= plugin_dir_url(__FILE__); ?>images/loader.gif" class="loading" style="position:absolute;" />
  
  <div class="widget" id="requests">	
    <div class="widget-top">
      <div class="widget-title"><h4>REQUESTS</h4></div>
	</div>
	<div class="widget-inside">
      <h2>0</h2>
	</div>
  </div>
  <div class="widget" id="opened">	
    <div class="widget-top">
      <div class="widget-title"><h4>OPENED</h4></div>
	</div>
	<div class="widget-inside">
      <h2>0%</h2>
	</div>
  </div>
  <div class="widget" id="clicked">	
    <div class="widget-top">
      <div class="widget-title"><h4>CLICKED</h4></div>
	</div>
	<div class="widget-inside">
      <h2>0%</h2>
	</div>
  </div>
  <div class="widget" id="others">	
	<div class="widget-inside">
      <div class="row clearfix">
        <div class="pull-left">
          <span class="square" style="background-color: rgb(188, 213, 22);"></span><span>DELIVERED</span>
        </div>
        <div id="delivered" class="pull-right">0%</div>
      </div>
      <div class="row clearfix">
        <div class="pull-left">
          <span class="square" style="background-color: rgb(255, 0, 224);"></span><span>BOUNCES</span>
        </div>
        <div id="bounces" class="pull-right">0%</div>
      </div>
      <div class="row clearfix">
        <div class="pull-left">
          <span class="square" style="background-color: rgb(62, 68, 192);"></span><span>UNSUBSCRIBES</span>
        </div>
        <div id="unsubscribes" class="pull-right">0%</div>
      </div>
      <div class="row clearfix">
        <div class="pull-left">
          <span class="square" style="background-color: rgb(224, 68, 40);"></span><span>SPAM REPORTS</span>
        </div>
        <div id="spam-reports" class="pull-right">0%</div>
      </div>
	</div>
  </div>
  
  <br style="clear:both;"/>
  
  <div id="sendgrid-stats"></div>
</div>
<div id="sendgrid-stats-legend"></div> 
