jQuery(document).ready(function($){
  /* Datepicker */
  var date = new Date();
  jQuery( "#sendgrid-start-date" ).datepicker({
    dateFormat: "yy/mm/dd",
    changeMonth: true,
    maxDate: _dateToYMD(new Date()),
    onClose: function( selectedDate ) {
      $( "#sendgrid-end-date" ).datepicker( "option", "minDate", selectedDate );
    }
  });
  var startDate = new Date(date.getFullYear(),date.getMonth(),date.getDate()-7);
  $('#sendgrid-start-date').datepicker("setDate", startDate);
  jQuery( "#sendgrid-end-date" ).datepicker({
    dateFormat: "yy/mm/dd",
    changeMonth: true,
    maxDate: _dateToYMD(new Date()),
    onClose: function( selectedDate ) {
      $( "#sendgrid-start-date" ).datepicker( "option", "maxDate", selectedDate );
    }
  });
  var endDate = new Date(date.getFullYear(),date.getMonth(),date.getDate());
  $('#sendgrid-end-date').datepicker("setDate", endDate);
  
  /* Apply filter */
  jQuery("#sendgrid-apply-filter").click(function(event) {
    event.preventDefault();

    var startDate = new Date(jQuery("#sendgrid-start-date").val());
    var endDate = new Date(jQuery("#sendgrid-end-date").val());

    getStats(_dateToYMD(startDate), _dateToYMD(endDate), 'sendgrid_get_stats');
  });
  
  /* Get Statistics and show chart */
  getStats(_dateToYMD(startDate), _dateToYMD(endDate), 'sendgrid_get_stats');
  
  function getStats(startDate, endDate, action)
  {
    $("#sendgrid-container #sendgrid-stats").html("");
    $("#sendgrid-container .loading").show();
    
    data = {
      action: action,
      start_date: _convertDateForRequest(startDate),
      end_date:   _convertDateForRequest(endDate),
      sendgrid_nonce: sendgrid_vars.sendgrid_nonce
    };

    $.post(ajaxurl, data, function(response) {
      console.log(response);
      var requestStats = [];
      var deliveredStats = [];
      var openStats = [];
      var uniqueOpenStats = [];
      var clickStats = [];
      var uniqueClickStats = [];
      var unsubscribeStats = [];
      var bounceStats = [];
      var spamreportStats = [];

      var requests      = 0;
      var opens         = 0;
      var clicks        = 0;
      var deliveres     = 0;
      var bounces       = 0;
      var unsubscribes = 0;
      var spamReports   = 0;

      response = jQuery.parseJSON(response);
      jQuery.each(response, function(key, value) {
        var date                 = new Date(_convertDateFromRequest(value.date)).getTime();
        var requestsThisDay      = value.requests ? value.requests : 0;
        var opensThisDay         = value.opens ? value.opens : 0;
        var clicksThisDay        = value.clicks ? value.clicks : 0;
        var deliveresThisDay     = value.delivered ? value.delivered : 0;
        var uniqueOpensThisDay   = value.unique_opens ? value.unique_opens : 0;
        var uniqueClicksThisDay  = value.unique_clicks ? value.unique_clicks : 0;
        var unsubscribersThisDay = value.unsubscribes ? value.unsubscribes : 0;
        var bouncesThisDay       = value.bounces ? value.bounces : 0;
        var spamReportsThisDay   = value.spamreports ? value.spamreports : 0;
        console.log(date);
        requests     += requestsThisDay;
        deliveres    += deliveresThisDay;
        opens        += opensThisDay;
        clicks       += clicksThisDay;
        bounces      += bouncesThisDay;
        unsubscribes += unsubscribersThisDay;
        spamReports  += spamReportsThisDay;
       
        requestStats.push([date, requestsThisDay]);
        deliveredStats.push([date, deliveresThisDay]);
        openStats.push([date, opensThisDay]);
        uniqueOpenStats.push([date, uniqueOpensThisDay]);
        clickStats.push([date, clicksThisDay]);
        uniqueClickStats.push([date, uniqueClicksThisDay]);
        unsubscribeStats.push([date, unsubscribersThisDay]);
        bounceStats.push([date, bouncesThisDay]);
        spamreportStats.push([date, spamReportsThisDay]);
      });
      
      // Config chart
      var data = [
        {
          label : 'Requests',
          data  : requestStats,
          points: { symbol: "circle" }
        },
        {
          label : 'Delivered',
          data  : deliveredStats,
          points: { symbol: "diamond" }
        },
        {
          label : 'Opens',
          data  : openStats,
          points: { symbol: "square" }
        },
        {
          label : 'Unique Opens',
          data  : uniqueOpenStats,
          points: { symbol: "triangle" }
        },
        {
          label : 'Clicks',
          data  : clickStats,
          points: { symbol: "cross" }
        },
        {
          label : 'Unique Clicks',
          data  : uniqueClickStats,
          points: { symbol: "circle" }
        },
        {
          label : 'Unsubscribes',
          data  : unsubscribeStats,
          points: { symbol: "diamond" }
        },
        {
          label : 'Bounces',
          data  : bounceStats,
          points: { symbol: "square" }
        },
        {
          label : 'Spam reports',
          data  : spamreportStats,
          points: { symbol: "triangle" }
        }       
      ];

      // Show chart
      $.plot("#sendgrid-stats", data, {
          xaxis: {
            mode: "time",
            minTickSize: [1, "day"],
            tickLength: 0,
            min: (new Date(startDate)).getTime(),
            max: (new Date(endDate)).getTime(),
            timeformat: "%b %d"
          },
          series: {
              lines: { show: true },
              points: { 
                radius: 4,
                show: true
              }
          },
          grid: {
            hoverable: true,
            borderWidth: 0
          },
          legend: {
            noColumns: 0,
            container: $("#sendgrid-stats-legend")
          },
          colors: ["#328701", "#bcd516", "#fba617", "#fbe500", "#1185c1", "#bcd0d1", "#3e44c0", "#ff00e0", "#e04428"]
      });
      
      // Show info in widgets
      var opensRate        = _round(((opens * 100) / deliveres), 2) + "%";
      var clicksRate       = _round(((clicks * 100) / deliveres), 2) + "%";
      var deliveresRate    = _round(((deliveres * 100) / requests), 2) + "%";
      var bouncesRate      = _round(((bounces * 100) / deliveres), 2) + "%";
      var unsubscribesRate = _round(((unsubscribes * 100) / deliveres), 2) + "%";
      var spamReportsRate  = _round(((spamReports * 100) / deliveres), 2) + "%";
      
      // Big containers
      $("#sendgrid-container #requests .widget-inside h2").html(requests);
      $("#sendgrid-container #opened .widget-inside h2").html(opensRate);
      $("#sendgrid-container #clicked .widget-inside h2").html(clicksRate);
      
      // Small container
      $("#sendgrid-container #others #delivered").html(deliveresRate);
      $("#sendgrid-container #others #bounces").html(bouncesRate);
      $("#sendgrid-container #others #unsubscribes").html(unsubscribesRate);
      $("#sendgrid-container #others #spam-reports").html(spamReportsRate);
      
      showInfo();
      $("#sendgrid-container .loading").hide();
    });
  }
  
  /* Flop chart tooltop */
  function showInfo()
  {
    var previousPoint = null;
    var previousLabel = null;

    $("#sendgrid-stats").bind("plothover", function (event, pos, item) {
      if (item) {
          if ((previousPoint != item.dataIndex) || (previousLabel != item.series.label)) {
              previousPoint = item.dataIndex;
              previousLabel = item.series.label;

              $("#flot-tooltip").remove();
              var date = _convertMonthToString(item.datapoint[0]);
              var value = item.datapoint[1];
              var color = item.series.color;

              showTooltip(item.pageX, item.pageY, 
                      "<b>" + date + "</b><br />" + item.series.label + ": " + value ,
                      color);
          }
      } else {
          $("#flot-tooltip").remove();
          previousPoint = null;
      }
    });
  }

  function showTooltip(x, y, contents, z) 
  {
      $('<div id="flot-tooltip">' + contents + '</div>').css({
          position: 'absolute',
          display: 'none',
          top: y - 30,
          left: x + 30,
          border: '2px solid',
          padding: '2px',
          'background-color': '#FFF',
          opacity: 0.80,
          'border-color': z,
          '-moz-border-radius': '5px',
          '-webkit-border-radius': '5px',
          '-khtml-border-radius': '5px',
          'border-radius': '5px'
      }).appendTo("body").fadeIn(200);
  }
  
  /**** Helpers ****/
  function _round(value, places) 
  {
      var multiplier = Math.pow(10, places);

      return (Math.round(value * multiplier) / multiplier);
  }
  
  function _dateToYMD(date) 
  {
      var d = date.getDate();
      var m = date.getMonth() + 1;
      var y = date.getFullYear();
      return '' + y + '/' + (m<=9 ? '0' + m : m) + '/' + (d <= 9 ? '0' + d : d);
  }
  
  function _convertMonthToString(timestamp) 
  {
    var month_names = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    var newDate = new Date(timestamp);
    var dateString = month_names[newDate.getMonth()] + " " + newDate.getDate();

    return dateString;
  }
  
  function _convertDateForRequest(str) {
    return str.replace(new RegExp("/", 'g'), "-");
  }
  
  function _convertDateFromRequest(str) {
    return str.replace(new RegExp("-", 'g'), "/");
  }
});