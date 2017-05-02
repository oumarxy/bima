

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Issued Date</th>
                        <th>Issued Name</th>
                        <th>Cover Note</th>
                        <th>Premium</th>
                        <th>Vat</th>
                        <th>Required Amount</th>
                        <th>Amount Paid</th>
                        <th>Receipt</th>
                        <th>Issued by</th>
                        <th>Status</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>          
                        <tr>
                            <td><?php
                            
                               $premium=Modules::run('insurance/get_where', $payment->id_insurance)->row()->premium;
                               $vat=Modules::run('insurance/get_where', $payment->id_insurance)->row()->vat;
                               $required=$premium+$vat;
                                $datestring = "%d/%m/%Y";
                                echo format_date($datestring, $payment->paid_on);
                                ?></td>
                            <td><?php echo htmlspecialchars(customer_by_id_insurance($payment->id_insurance), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(cover_note_by_id($payment->id_insurance), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo number_format($premium); ?></td>
                            <td><?php echo ($vat>0) ? number_format($vat) : '-'; ?></td>
                            <td><?php echo number_format($required); ?></td>
                            <td><?php echo number_format($payment->amount); ?></td>
                            <td><?php echo htmlspecialchars($payment->receipt, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php $user = $this->ion_auth->user($payment->user_id)->row();
                                echo htmlspecialchars($user->first_name . ' ' . $user->last_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo ($payment->status) ? anchor('payment/unconfirm/' . $payment->id_payment, '<span class="label label-success">' . confirmed_status_by_id($payment->status) . '</span>') : anchor('payment/confirm/' . $payment->id_payment, '<span class="label label-danger">' . confirmed_status_by_id($payment->status) . '</span>'); ?></td>
                            <td><?php echo anchor("payment/edit/" . $payment->id_payment, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?> | <?php echo anchor("payment/delete/" . $payment->id_payment, '<i class="fa fa-trash"></i>', array('title'=>"Delete",'onclick' => "return confirm('Do you want to delete this record?')")); ?> | <?php echo anchor("payment/receipt/" . $payment->id_payment, '<i class="fa fa-file-pdf-o"></i>', array('title'=>"Receipt")); ?></td>
                        </tr>

<?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Issued Date</th>
                        <th>Issued Name</th>
                        <th>Cover Note</th>
                        <th>Premium</th>
                        <th>Vat</th>
                        <th>Required Amount</th>
                        <th>Amount Paid</th>
                        <th>Receipt</th>
                        <th>Issued by</th>
                        <th>Status</th>
                        <th>Options</th>
                    </tr>
                </tfoot>
            </table>
            <p><?php echo anchor('payment/add', '<i class="fa fa-plus-circle"></i> Add payment') ?> | <?php echo anchor('payment/confirm/all', '<i class="fa fa-plus-circle"></i> Confirm all',array('title'=>"Confirm All",'onclick' => "return confirm('Do you want confirm all records')")) ?> | <?php echo anchor('payment/unconfirm/all', '<i class="fa fa-plus-circle"></i> Unconfirm all',array('title'=>"Unconfirm All",'onclick' => "return confirm('Do you want unconfirm all records')")) ?> </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>