<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3><?php echo $customer ?></h3>
                <p>Active Customers</p>
            </div>
            <div class="icon">
                <i class="ion ion-ios-people"></i>
            </div>
            <?php echo anchor('customer/active_customer', 'View all <i class="fa fa-arrow-circle-right"></i>', 'class="small-box-footer"') ?>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3><?php echo $claim ?></h3>
                <p>Claims</p>
            </div>
            <div class="icon">
                <i class="ion ion-stats-bars"></i>
            </div>
            <?php echo anchor('claim/sheet', 'View all <i class="fa fa-arrow-circle-right"></i>', 'class="small-box-footer"') ?>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3><?php echo $insurer; ?> </h3>
                <p>Active Insurer</p>
            </div>
            <div class="icon">
                <i class="ion ion-ios-briefcase"></i>
            </div>
            <?php echo anchor('insurer/active_insurer', 'View all <i class="fa fa-arrow-circle-right"></i>', 'class="small-box-footer"') ?>
        </div>
    </div><!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <?php
                $today = date_to_int_string(date('d/m/Y'));
                $sql = "select count(*) as expired from insurance where id_domain=".$this->ion_auth->get_id_domain()." and  expired_on < $today and closed<1 ";
                ?>
                <h3><?php echo Modules::run('insurance/_custom_query', $sql)->row()->expired; ?></h3>
                <p>Expired Covers</p>
            </div>
            <div class="icon">
                <i class="ion ion-pie-graph"></i>
            </div>
            <?php echo anchor('insurance/index/expired', 'View all <i class="fa fa-arrow-circle-right"></i>', 'class="small-box-footer"') ?>
        </div>
    </div><!-- ./col -->
</div><!-- /.row -->
<!-- Main row -->
<div class="row">

    <div class="col-md-6">
      <!-- LINE CHART -->
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title">Premium Remmitance <?php echo date('Y'); ?> vs <?php echo date('Y')-1; ?></h3>
          <div class="box-tools pull-right">
            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="lineChart" style="height:250px"></canvas>
          </div>
        </div><!-- /.box-body -->
      </div><!-- /.box -->
    </div>
    <!-- /.col (LEFT) -->
    <div class="col-md-6">

      <!-- BAR CHART -->
      <div class="box box-success">
        <div class="box-header with-border">
          <h3 class="box-title">Commission Collection</h3>
          <div class="box-tools pull-right">
            <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
          </div>
        </div>
        <div class="box-body">
          <div class="chart">
            <canvas id="barChart" style="height:230px"></canvas>
          </div>
        </div><!-- /.box-body -->
      </div>
      <!-- /.box -->

    </div>
    <!-- /.col (RIGHT) -->

</div>

</section><!-- right col -->
</div><!-- /.row (main row) -->
