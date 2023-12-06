<!DOCTYPE html>
<html lang="ja">
<head>
  <script src="/common/js/jquery-2.0.2.min.js"></script>
  <link href="/favicon.ico" type="image/x-icon" rel="icon"/>
  <link href="/favicon.ico" type="image/x-icon" rel="shortcut icon"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js" type="text/javascript"></script>

  <meta charset="UTF-8">
  <title>CapturesPy</title>

  <link rel="stylesheet"
        href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css"
        integrity="sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ=="
        crossorigin="anonymous">

  <style media="screen">

    .container {
      width: 100%;
      position: relative;
      height: auto !important;
      height: 100%;
      min-height: 100%;
    }

    #footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      height: 20px;
      text-align: center;
      background-color: #f8f8f8;
      color: #fff;
      margin-left: -15px;
    }

    /* 表示文字の装飾 */
    div.tooltip1{
        display: inline-block;                        /* インライン要素化 */
    }

    /* ツールチップ部分を隠す */
    div.tooltip1 span {
        display: none;

        position: absolute;            /* relativeからの絶対位置 */
        top: 40px;
        right: 20px;
        font-size: 90%;
        background-color: rgba(230, 230, 230, 0.9);
        padding: 20px;
        border-radius:3px;
        border-style: solid;
        border-width: 1px;
        z-index:100;

        width: 600px;
        height:600px;
        overflow-y: scroll;

    }

  </style>

</head>
<body>
<header>
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <div class="navbar-header">
        <a href="/">
          <img src="/common/img/icon.png" height="45px" alt="">
        </a>
      </div>

      <div class="tooltip1 navbar-header" style="float:right;padding-top:15px;">
        <a href="#" onclick="toggleAbout();" ><img src="/common/img/first_mark.png" alt="CapturesPyとは" border="0">CapturesPyとは</a>

        <span id="about">
          {val about}
        </span>
      </div>

    </div>
  </nav>
</header>
<div class="container">
  <form name="fMain" action="index.php" method="GET">

    <div align="center" id="graph-div">
      <div id="chart_title-0">
        <div style="float:left;"><select id="url" name="url" class="form-control" onchange="return changeUrl();" style="width: 200px;">{val url}</select></div>
        <div style="float:left; padding: 7px 20px 0 20px;">差分率遷移グラフ</div>
        <div style="float:right;"><select id="range" name="range" class="form-control" onchange="return changeUrl();" style="width: 200px;">{val range}</select></div>
        <div style="float:right; padding: 7px 20px 0 20px;">集計期間</div>
      </div>

      <canvas id="graph-area" style="width: 60%;"></canvas>
    </div>

    <div id="footer">
      <p class="text-muted">&copy;2016 crossfinity. All rights reserved.</p>
    </div>
  </form>
</div>

<script>
  function changeUrl(){
    var url = "index.php?url=" + $("#url").val() + "&range=" + $("#range").val() ;
    location.href = url;
  }

  // ▼グラフの中身
  var data = {
    labels: [{val graph/label}],
    datasets: [

      {
        label: "Elements PC",
        lineTension: 0.1,
        borderWidth: 2,
        backgroundColor: 'rgba({val COLORS/ELEMENTS/PC},0.4)',
        borderColor: 'rgba({val COLORS/ELEMENTS/PC},1)',
        pointBorderColor: 'rgba({val COLORS/ELEMENTS/PC},1)',
        pointBackgroundColor: 'rgba({val COLORS/ELEMENTS/PC},1)',
        pointHoverBackgroundColor: 'rgba({val COLORS/ELEMENTS/PC},1)',
        fill: false,
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        radius: 1,
        pointRadius: 1,
        pointHitRadius: 10,
        data: [{val graph/pc/complete}]       // 各点の値
      },

      {
        label: "Elements SP",
        lineTension: 0.1,
        borderWidth: 2,
        backgroundColor: 'rgba({val COLORS/ELEMENTS/SP},0.4)',
        borderColor: 'rgba({val COLORS/ELEMENTS/SP},1)',
        pointBorderColor: 'rgba({val COLORS/ELEMENTS/SP},1)',
        pointBackgroundColor: 'rgba({val COLORS/ELEMENTS/SP},1)',
        pointHoverBackgroundColor: 'rgba({val COLORS/ELEMENTS/SP},1)',
        fill: false,
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        radius: 1,
        pointRadius: 1,
        pointHitRadius: 10,
        data: [{val graph/sp/complete}]       // 各点の値
      },

      {
        label: "Sources   PC",
        lineTension: 0.1,
        borderWidth: 2,
        backgroundColor: 'rgba({val COLORS/SOURCES/PC},0.4)',
        borderColor: 'rgba({val COLORS/SOURCES/PC},1)',
        pointBorderColor: 'rgba({val COLORS/SOURCES/PC},1)',
        pointBackgroundColor: 'rgba({val COLORS/SOURCES/PC},1)',
        pointHoverBackgroundColor: 'rgba({val COLORS/SOURCES/PC},1)',
        fill: false,
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        radius: 1,
        pointRadius: 1,
        pointHitRadius: 10,
        data: [{val graph/pc/simple}]       // 各点の値
      },

      {
        label: "Sources   SP",
        lineTension: 0.1,
        borderWidth: 2,
        backgroundColor: 'rgba({val COLORS/SOURCES/SP},0.4)',
        borderColor: 'rgba({val COLORS/SOURCES/SP},1)',
        pointBorderColor: 'rgba({val COLORS/SOURCES/SP},1)',
        pointBackgroundColor: 'rgba({val COLORS/SOURCES/SP},1)',
        pointHoverBackgroundColor: 'rgba({val COLORS/SOURCES/SP},1)',
        fill: false,
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderWidth: 1,
        pointHoverRadius: 5,
        pointHoverBorderWidth: 2,
        radius: 1,
        pointRadius: 1,
        pointHitRadius: 10,
        data: [{val graph/sp/simple}]       // 各点の値
      }

    ]
  };


  window.onload = function () {
    createChart();
  }


  function createChart() {
    var ctx, myChart;
    ctx = document.getElementById('graph-area');
    if (ctx !== null) {

      myChart = new Chart.Line(ctx, {
        data: data,
        options: {
          scales: {
            yAxes: [
              {
                ticks: {
                  beginAtZero: true,
                  min: 0,
                  max: 100
                }
              }
            ]
          },
          // ツールチップの設定
          tooltips: {
            mode: 'label',
            callbacks: {
              label: function (tooltipItem, data) {
                var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
                var val = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                return datasetLabel + ' ： ' + val + ' %';
              }
            }
          }
        }
      });
    }

    ctx.onclick = function(evt)
    {
        var activePoints = myChart.getElementsAtEvent(evt);

        if(activePoints.length > 0)
        {
          //get the internal index of slice in pie chart
          var index = activePoints[0]["_index"];
          var dt = data["labels"][index];
          var dts = dt.split('/');

          var now = new Date();
          var year = now.getFullYear();
          var mon = now.getMonth()+1;
          var day = now.getDate();

          if((dts[0] * 1) > mon && (dts[1] * 1) > day){
            //前年
            year--;
          }

          dt = year + "-" + dts[0] + "-" + dts[1];
          var url = "detail.php?dt=" + dt + "&url_id=" + $("#url").val();

          window.open(url,"detail","width=1200,height=900,resizable=yes,scrollbars=yes");

       }
    }


  }

  function toggleAbout(){
    var trg = $("#about");

    if(trg.css("display") == "none"){
      trg.show(400);
    } else {
      trg.hide(400);
    }

  }

</script>

</body>
</html>
