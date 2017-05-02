

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" >  <?php //echo $message;                       ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <?php if ($this->input->post('transaction_type') == '') { ?>
                            <th>Type</th>
                        <?php } ?>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Bank</th>
                        <th>Description</th>
                        <th>Cheque</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bank_transactions as $bank_transaction): ?>   
                        <tr>
                            <?php if ($this->input->post('transaction_type') == '') { ?>
                                <td><?php echo Modules::run('transaction_type/get_where', $bank_transaction->id_transaction_type)->row()->type; ?></td>
                            <?php } ?>
                            <td><?php echo format_date('', $bank_transaction->issued_on); ?></td>
                            <td><?php echo $bank_transaction->particular; ?></td>
                            <td><?php echo Modules::run('bank_account/get_where_custom', array('id_bank_account' => $bank_transaction->id_bank_account, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->bank_name; ?></td>
                            <td><?php echo $bank_transaction->comment; ?></td>
                            <td><?php echo $bank_transaction->cheque_number; ?></td>
                            <td><?php echo ($bank_transaction->id_transaction_type == 1) ? number_format($bank_transaction->amount) : ''; ?></td>
                            <td><?php echo ($bank_transaction->id_transaction_type == 1) ? '' : number_format($bank_transaction->amount); ?></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <?php if ($this->input->post('transaction_type') == '') { ?>
                            <th>Type</th>
                        <?php } ?>
                        <th>Date</th>
                        <th>Particular</th>
                        <th>Bank</th>
                        <th>Description</th>
                        <th>Cheque</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                </tfoot>
            </table>
            <p>
                <?php if (count($bank_transactions) > 0): ?>
                    <?php echo form_open('report/print_bank_transaction_pdf', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $id_transaction_type; ?>" name="transaction_type"/>
                    <input type="hidden" value="<?php echo $id_bank_account; ?>" name="bank_account"/>
                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="pdf" value="Export to PDF"/>
                    <?php echo form_close(); ?>

                    &nbsp; &nbsp; &nbsp;

                    <?php echo form_open('report/print_bank_transaction_excel', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $id_transaction_type; ?>" name="transaction_type"/>
                    <input type="hidden" value="<?php echo $id_bank_account; ?>" name="bank_account"/>
                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="excel" value="Export to Excel"/>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>