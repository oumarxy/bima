

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Reported Date</th>
                        <th>Particular</th>
                         <th>Contacts</th>
                        <th>Property</th>
                        <th>Nature</th>
                        <th>Insurer</th>
                        <th>Claim Amount</th>
                        <th>Settlement Amount</th>
                        <th>Paid</th>
                        <th>Remark</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $claim): ?>          
                        <tr>
                            <td><?php
                                echo format_date('', $claim->reported_on);
                                ?></td>
                            <td><?php echo htmlspecialchars(customer_by_id_insurance($claim->id_insurance), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo Modules::run('customer/get_where',Modules::run('property/get_where',Modules::run('insurance/get_where',$claim->id_insurance)->row()->id_property)->row()->id_customer)->row()->phone; ?></td>
                            <td><?php echo Modules::run('property/get_where',Modules::run('insurance/get_where',$claim->id_insurance)->row()->id_property)->row()->number; ?></td>
                            <td><?php echo htmlspecialchars(claim_type_by_id($claim->id_claim_type), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo insurer_by_id_insurance($claim->id_insurance); ?></td>
                            <td><?php echo number_format($claim->claim_amount, 0); ?></td>
                            <td><?php echo number_format($claim->settlement_amount, 0); ?></td>
                            <td><?php echo ($claim->paid) ? anchor('claim/not_paid/' . $claim->id_claim, paid_status_by_id($claim->paid),array('title'=>"Set to not paid",'onclick' => "return confirm('Do you want to set to not paid?')")) : anchor('claim/paid/' . $claim->id_claim, paid_status_by_id($claim->paid),array('title'=>"Set to paid",'onclick' => "return confirm('Do you want to set to paid?')")); ?></td>
                            <td><?php echo ($claim->remark==1) ? anchor('claim/open/' . $claim->id_claim, remark_by_id($claim->remark),array('title'=>"Open claim",'onclick' => "return confirm('Do you want to open this claim?')")) : anchor('claim/close/' . $claim->id_claim, remark_by_id($claim->remark),array('title'=>"Close claim",'onclick' => "return confirm('Do you want close this claim?')")); ?></td>
                            <td><?php echo anchor("claim/edit/" . $claim->id_claim, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?> | <?php echo anchor("claim/delete/" . $claim->id_claim, '<i class="fa fa-trash"></i>',  array('title'=>"Delete",'onclick' => "return confirm('Do you want to delete this record?')")); ?></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>

                        <th>Reported Date</th>
                        <th>Particular</th>
                        <th>Contacts</th>
                        <th>Property</th>
                        <th>Nature</th>
                        <th>Insurer</th>
                        <th>Claim Amount</th>
                        <th>Settlement Amount</th>
                         <th>Paid</th>
                        <th>Remark</th>
                        <th>Options</th>
                    </tr>
                </tfoot>
            </table>
            <p><?php echo anchor('claim/register', '<i class="fa fa-plus-circle"></i> Register claim') ?> </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
