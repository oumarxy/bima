<?php $level1url = $this->uri->segment(1); ?>
</div>
</section><!-- /.content -->
</div><!-- /.content-wrapper -->


<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="pull-right hidden-xs">
        Version 1.0
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; <?php echo date('Y'); ?>  <a href="http://smartwebstz.com" target="_blank">Smartweb Solutions</a></strong>.  All rights reserved.
</footer>
</div><!-- ./wrapper -->

<!-- REQUIRED JS SCRIPTS -->

<!-- jQuery 2.1.4 -->
<script src="<?php echo base_url(); ?>media/plugins/jQuery/jQuery-2.1.4.min.js"></script>

<!-- Bootstrap 3.3.4 -->
<script src="<?php echo base_url(); ?>media/bootstrap/js/bootstrap.min.js"></script>
<?php if (trim($level1url) == 'site') { ?>
        <!-- ChartJS 1.0.1 -->
    <script src="<?php echo base_url(); ?>media/plugins/chartjs/Chart.min.js"></script>

<?php } ?>

<!-- AdminLTE App -->
<script src="<?php echo base_url(); ?>media/dist/js/app.min.js"></script>

<!-- ajax_lack_func-->
<script src="<?php echo base_url(); ?>media/ajax/ajax_lack_func.js"></script>


<!-- DataTables -->
<script src="<?php echo base_url(); ?>media/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url(); ?>media/plugins/datatables/dataTables.bootstrap.min.js"></script>
<!-- SlimScroll -->
<script src="<?php echo base_url(); ?>media/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- Select2 -->
<script src="<?php echo base_url(); ?>media/plugins/select2/select2.full.min.js"></script>

<!-- InputMask -->
<script src="<?php echo base_url(); ?>media/plugins/input-mask/jquery.inputmask.js"></script>
<script src="<?php echo base_url(); ?>media/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="<?php echo base_url(); ?>media/plugins/input-mask/jquery.inputmask.extensions.js"></script>

<!-- date-range-picker -->
<script src="<?php echo base_url(); ?>media/ajax/libs/moment.js/moment.min.js"></script>
<script src="<?php echo base_url(); ?>media/plugins/daterangepicker/daterangepicker.js"></script>

<!-- iCheck 1.0.1 -->
<script src="<?php echo base_url(); ?>media/plugins/iCheck/icheck.min.js"></script>

<!-- FastClick -->
<script src="<?php echo base_url(); ?>media/plugins/fastclick/fastclick.min.js"></script>
<?php if (trim($level1url) == 'site') { ?>

<?php
$year=date('Y');
$last_year=$year-1;

    $sql = "select FROM_UNIXTIME( insurance.comm_on,'%M') as month,sum(insurance.premium) as premium,sum(insurance.vat) as vat,
          sum(insurance.commission) as commission from
          insurance  where FROM_UNIXTIME(insurance.comm_on,'%Y') = '$last_year'  and insurance.paid=1  and insurance.id_domain=".$this->ion_auth->get_id_domain()."
          group by (FROM_UNIXTIME( insurance.comm_on,'%M')) order by insurance.comm_on";
              $insurances = Modules::run('insurance/_custom_query', $sql)->result_array();
              $month_data=array();
              $last_premium_data=array();
              $last_commission_data=array();
              foreach ($insurances as $insurance) {
                $total=$insurance['premium']+$insurance['vat'];
                array_push($month_data,$insurance['month']);
                array_push($last_premium_data,$total);
                array_push($last_commission_data,$insurance['commission']);
              }

   $sql = "select FROM_UNIXTIME( insurance.comm_on,'%M') as month,sum(insurance.premium) as premium,sum(insurance.vat) as vat,
   sum(insurance.commission) as commission from
   insurance  where FROM_UNIXTIME(insurance.comm_on,'%Y') = '$year'  and insurance.paid=1  and insurance.id_domain=".$this->ion_auth->get_id_domain()."
   group by (FROM_UNIXTIME( insurance.comm_on,'%M')) order by insurance.comm_on";
    $insurances = Modules::run('insurance/_custom_query', $sql)->result_array();
    $premium_data=array();
    $vat_data=array();
    $commission_data=array();
    foreach ($insurances as $insurance) {
        $total=$insurance['premium']+$insurance['vat'];
        array_push($premium_data,$total);
        array_push($commission_data,$insurance['commission']);
    }

     ?>

    <!-- page script -->
    <script>

      $(function () {
        /* ChartJS
         * -------
         * Here we will create a few charts using ChartJS
         */

         var areaChartData = {
           labels: <?php echo json_encode($month_data) ?>,
           datasets: [
             {
               label: "Premium 2016",
               fillColor: "rgba(60,141,188,0.9)",
               strokeColor: "rgba(60,141,188,0.8)",
               pointColor: "#3b8bba",
               pointStrokeColor: "rgba(60,141,188,1)",
               pointHighlightFill: "#fff",
               pointHighlightStroke: "rgba(60,141,188,1)",
               data: <?php echo json_encode($premium_data)?>
             },
             {
               label: "Premium 2015",
               fillColor: "rgba(210, 214, 222, 1)",
               strokeColor: "rgba(210, 214, 222, 1)",
               pointColor: "rgba(210, 214, 222, 1)",
               pointStrokeColor: "#c1c7d1",
               pointHighlightFill: "#fff",
               pointHighlightStroke: "rgba(220,220,220,1)",
               data: <?php echo json_encode($last_premium_data)?>
             }
           ]
         };

         var areaChartOptions = {
           //Boolean - If we should show the scale at all
           showScale: true,
           //Boolean - Whether grid lines are shown across the chart
           scaleShowGridLines: false,
           //String - Colour of the grid lines
           scaleGridLineColor: "rgba(0,0,0,.05)",
           //Number - Width of the grid lines
           scaleGridLineWidth: 1,
           //Boolean - Whether to show horizontal lines (except X axis)
           scaleShowHorizontalLines: true,
           //Boolean - Whether to show vertical lines (except Y axis)
           scaleShowVerticalLines: true,
           //Boolean - Whether the line is curved between points
           bezierCurve: true,
           //Number - Tension of the bezier curve between points
           bezierCurveTension: 0.3,
           //Boolean - Whether to show a dot for each point
           pointDot: false,
           //Number - Radius of each point dot in pixels
           pointDotRadius: 4,
           //Number - Pixel width of point dot stroke
           pointDotStrokeWidth: 1,
           //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
           pointHitDetectionRadius: 20,
           //Boolean - Whether to show a stroke for datasets
           datasetStroke: true,
           //Number - Pixel width of dataset stroke
           datasetStrokeWidth: 2,
           //Boolean - Whether to fill the dataset with a color
           datasetFill: true,
           //String - A legend template
           legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
           //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
           maintainAspectRatio: true,
           //Boolean - whether to make the chart responsive to window resizing
           responsive: true
         };

        //-------------
        //- LINE CHART -
        //--------------
        var lineChartCanvas = $("#lineChart").get(0).getContext("2d");
        var lineChart = new Chart(lineChartCanvas);
        var lineChartOptions = areaChartOptions;
        lineChartOptions.datasetFill = false;
        lineChart.Line(areaChartData, lineChartOptions);

       // bar graph parameters
        var areaChartData = {
          labels: <?php echo json_encode($month_data) ?>,
          datasets: [
            {
              label: "Commission 2016",
              fillColor: "rgba(60,141,188,0.9)",
              strokeColor: "rgba(60,141,188,0.8)",
              pointColor: "#3b8bba",
              pointStrokeColor: "rgba(60,141,188,1)",
              pointHighlightFill: "#fff",
              pointHighlightStroke: "rgba(60,141,188,1)",
              data: <?php echo json_encode($commission_data)?>
            },
            {
              label: "Commission 2015",
              fillColor: "rgba(210, 214, 222, 1)",
              strokeColor: "rgba(210, 214, 222, 1)",
              pointColor: "rgba(210, 214, 222, 1)",
              pointStrokeColor: "#c1c7d1",
              pointHighlightFill: "#fff",
              pointHighlightStroke: "rgba(220,220,220,1)",
              data: <?php echo json_encode($last_commission_data)?>
            }
          ]
        };

        //-------------
        //- BAR CHART -
        //-------------
        var barChartCanvas = $("#barChart").get(0).getContext("2d");
        var barChart = new Chart(barChartCanvas);
        var barChartData = areaChartData;
        barChartData.datasets[1].fillColor = "#00a65a";
        barChartData.datasets[1].strokeColor = "#00a65a";
        barChartData.datasets[1].pointColor = "#00a65a";
        var barChartOptions = {
          //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
          scaleBeginAtZero: true,
          //Boolean - Whether grid lines are shown across the chart
          scaleShowGridLines: true,
          //String - Colour of the grid lines
          scaleGridLineColor: "rgba(0,0,0,.05)",
          //Number - Width of the grid lines
          scaleGridLineWidth: 1,
          //Boolean - Whether to show horizontal lines (except X axis)
          scaleShowHorizontalLines: true,
          //Boolean - Whether to show vertical lines (except Y axis)
          scaleShowVerticalLines: true,
          //Boolean - If there is a stroke on each bar
          barShowStroke: true,
          //Number - Pixel width of the bar stroke
          barStrokeWidth: 2,
          //Number - Spacing between each of the X value sets
          barValueSpacing: 5,
          //Number - Spacing between data sets within X values
          barDatasetSpacing: 1,
          //String - A legend template
          legendTemplate: "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].fillColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",
          //Boolean - whether to make the chart responsive
          responsive: true,
          maintainAspectRatio: true
        };

        barChartOptions.datasetFill = false;
        barChart.Bar(barChartData, barChartOptions);
      });
    </script>

<?php } ?>


<!-- page script -->

<script>
    $(function () {

        $("#example1").DataTable();
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false
        });

        //Initialize Select2 Elements
        $(".select2").select2();

        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //Money Euro
        $("[data-mask]").inputmask();

        //Date range picker
        $('#reservation').daterangepicker();
        //Date range picker with time picker
        $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY'});

        //Date range picker
        $('#reservation1').daterangepicker();
        //Date range picker with time picker
        $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY'});



        //iCheck for checkbox and radio inputs
        $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
            checkboxClass: 'icheckbox_minimal-blue',
            radioClass: 'iradio_minimal-blue'
        });
        //Red color scheme for iCheck
        $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
            checkboxClass: 'icheckbox_minimal-red',
            radioClass: 'iradio_minimal-red'
        });
        //Flat red color scheme for iCheck
        $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
            checkboxClass: 'icheckbox_flat-green',
            radioClass: 'iradio_flat-green'
        });

        //Timepicker
        $(".timepicker").timepicker({
            showInputs: false
        });
    });
</script>



</body>
</html>
