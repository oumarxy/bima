
<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" >  <?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>

                        <th>Issued Name</th>
                        <th>Insurer</th>
                        <th>Cover Note</th>
                        <th>Sticker</th>
                        <th>Property</th>
                        <th>Premium Amount</th>
                        <th>Vat</th>
                         <th>Amount Required</th>
                        <th>Expire</th>
                        <th>Status</th>
                        <th>Options</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($insurances as $insurance): ?>
                        <tr>
                            <td><?php echo anchor(site_url('customer/view/' . customer_id_by_id_property($insurance->id_property)), customer_by_id_property($insurance->id_property), 'title="View owner"'); ?></td>
                            <td><?php echo insurer_by_id($insurance->id_insurer); ?></td>
                            <td><?php echo $insurance->cover_note; ?></td>
                             <td><?php echo $insurance->sticker_number; ?></td>
                            <td><?php
                            if(Modules::run("property/get_where",$insurance->id_property)->num_rows()>0){
                            $property=($insurance->type==1) ? Modules::run("property/get_where",$insurance->id_property)->row()->number:Modules::run("property/get_where",$insurance->id_property)->row()->description ;  echo  anchor(site_url('property/view/' . $insurance->id_property), $property, 'View Property');
                            }?></td>
                            <td><?php echo number_format($insurance->premium); ?></td>
                            <td><?php echo ($insurance->vat>0) ? number_format($insurance->vat) : '-'; ?></td>
                            <td><?php echo number_format($insurance->premium+$insurance->vat); ?></td>
                            <td><?php echo format_date('', $insurance->expired_on); ?></td>
                            <td><?php echo ($insurance->paid) ? '<span class="label label-success">' . paid_status_by_id($insurance->paid) . '</span>' : '<span class="label label-danger">' . paid_status_by_id($insurance->paid) . '</span>'; ?></td>
                            <td><?php if($insurance->expired_on > time()){
                            echo anchor("insurance/edit/" . $insurance->id_insurance, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?> |
                                <?php echo anchor("insurance/delete/" . $insurance->id_insurance, '<i class="fa fa-trash"></i>', array('title'=>"Delete",'onclick' => "return confirm('This will delete payment and claim records associated to this insurance. Do you want to delete?')"));?> |
                            <?php echo anchor("claim/register/" . $insurance->id_insurance, '<i class="fa fa-warning"></i>', 'title="Register Claim"');}?>
                                <?php if(((round(($insurance->expired_on-time()))/60/60/24/30)<1 || $insurance->expired_on <= time()) && !$insurance->closed){
                                  echo anchor("insurance/renew/" . $insurance->id_insurance, '<i class="fa fa-credit-card"></i>', 'title="Renew"');
                                  echo " | ";
                                  echo anchor("sms/notify/" . $insurance->id_insurance, '<i class="fa fa-envelope-o"></i>', 'title="Send Message"');
                                 }?>
                                </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr>

                        <th>Issued Name</th>
                        <th>Insurer</th>
                        <th>Cover Note</th>
                        <th>Sticker</th>
                        <th>Property</th>
                        <th>Premium Amount</th>
                        <th>Vat</th>
                         <th>Amount Required</th>
                        <th>Expire</th>
                        <th>Status</th>
                        <th>Options</th>
                    </tr>
                </tfoot>
            </table>
          <p><?php echo anchor('insurance/register', '<i class="fa fa-plus-circle"></i> Register insurance') ?> | <?php echo anchor('insurance/import', '<i class="fa fa-plus-circle"></i> Import insurance') ?> </p>
        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
