

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Bank</th>
                        <th>Description</th>
                        <th>Cheque</th>
                        <th>Amount</th>
                        <th>Recorder</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bank_transactions as $bank_transaction): ?>          
                        <tr>
                            <td><?php echo Modules::run('transaction_type/get_where', $bank_transaction->id_transaction_type)->row()->type; ?></td>
                            <td><?php
                                $datestring = "%d/%m/%Y";
                                echo format_date($datestring, $bank_transaction->issued_on);
                                ?></td>
                            <td><?php echo $bank_transaction->particular; ?></td>
                            <td><?php echo Modules::run('bank_account/get_where', $bank_transaction->id_bank_account)->row()->bank_name; ?></td>
                            <td><?php echo $bank_transaction->comment; ?></td>
                            <td><?php echo $bank_transaction->cheque_number; ?></td>
                            <td><?php echo number_format($bank_transaction->amount); ?></td>
                             <td><?php
                                $user = $this->ion_auth->user($bank_transaction->user_id)->row();
                                echo htmlspecialchars($user->first_name . ' ' . $user->last_name, ENT_QUOTES, 'UTF-8');
                                ?></td>
                            <td><?php echo anchor("bank_transaction/edit/" . $bank_transaction->id_bank_transaction, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?> | <?php echo anchor("bank_transaction/delete/" . $bank_transaction->id_bank_transaction, '<i class="fa fa-trash"></i>', array('title' => "Delete", 'onclick' => "return confirm('Do you want to delete this record?')")); ?></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Bank</th>
                        <th>Description</th>
                        <th>Cheque</th>
                        <th>Amount</th>
                        <th>Recorder</th>
                        <th>Options</th>
                    </tr>
                </tfoot>
            </table>
            <p><?php echo anchor('bank_transaction/add/1', '<i class="fa fa-plus-circle"></i> Record Deposit') ?> | <?php echo anchor('bank_transaction/add/2', '<i class="fa fa-plus-circle"></i> Record Withdraw') ?>  </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>