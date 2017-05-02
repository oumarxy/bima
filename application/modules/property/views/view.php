<!-- Main content -->
        <section class="invoice">
          <!-- info row -->
          <div class="row invoice-info">
            <div class="col-sm-4 invoice-col">
              Owner
              <address>
                  <strong><?php echo $customer->name ?></strong><br>
                <?php echo $customer->address?><br>
                Contact: <?php echo $customer->phone?><br>
                Status: <?php echo status_by_id($customer->status)?>
              </address>
            </div><!-- /.col -->
            <div class="col-sm-4 invoice-col">
            </div><!-- /.col -->
            <div class="col-sm-4 invoice-col">
              <b>Property Info</b><br>
              <br>
              <b>Type:</b> <?php echo Modules::run('property_type/get_where',$property->id_property_type)->row()->type;?><br>
              <b>Value:</b> <?php echo is_numeric($property->property_value) ? format_currency($property->property_value) : $property->property_value;?><br>
              <!--<b>Year:</b> <?php echo $property->year ?>-->
            </div><!-- /.col -->
          </div><!-- /.row -->

          <!-- Table row -->
          <div class="row">
            <div class="col-xs-12 table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Type</th>
                   <!-- <th>Usage</th>-->
                    <th>Property</th>
                    <th>claim</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                      <td><?php echo Modules::run('property_type/get_where',$property->id_property_type)->row()->type;?></td>
                  <!--  <td><?php echo vehicle_class_by_id($property->class);?></td>-->
                    <td><?php echo ($property->id_property_type==1) ? $property->number : $property->description;?></td>
                     <td><?php echo (!$property->claim) ? '<span class="label label-success">'. claim_status_by_id($property->claim).'</span>' : '<span class="label label-danger">'.  claim_status_by_id($property->claim).'</span>';?></td>
                  </tr>
                </tbody>
              </table>
            </div><!-- /.col -->
          </div><!-- /.row -->

          <!-- this row will not appear when printing -->
          <div class="row no-print">
            <div class="col-xs-12">
                <?php echo anchor('#', '<i class="fa fa-print"></i> Print','target="_blank" class="btn btn-default"') ?>
              <button class="btn btn-primary pull-right" style="margin-right: 5px;"><i class="fa fa-download"></i> Generate PDF</button>
            </div>
          </div>
        </section><!-- /.content -->