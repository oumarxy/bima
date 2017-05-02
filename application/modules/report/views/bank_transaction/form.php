<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php //echo $message; ?>
            </div> 

            <div class="form-group">
                <label for="inputDaterange" class="col-sm-4 control-label">Issued: </label>
                <div class="col-sm-8"><?php echo form_input($date_range); ?></div>
            </div>    


            <div class="form-group">
                <label for="inputTransactiontype" class="col-sm-4 control-label"> Transaction Type: </label>
                <div class="col-sm-8">
                    <select name="transaction_type" class="form-control select2" style="width: 64%;">   
                        <option value="">Select Transaction Type</option>
                        <?php
                        foreach ($transaction_type_list as $transaction_type) {
                            echo '<option value="' . $transaction_type['id_transaction_type'] . '" >' . $transaction_type['type'] . '</option>';
                        }
                        ?>    

                    </select>   

                </div>
            </div>      

            <div class="form-group">
                <label  class="col-sm-4 control-label"> Account: </label>
                <div class="col-sm-8">
                    <select name="bank_account" class="form-control select2" style="width: 70%;">   
                        <option value="">Select Account</option>
                        <?php
                        foreach ($bank_account_list as $bank_account) {
                            echo '<option value="' . $bank_account['id_bank_account'] . '" ' . $selected . '>' . $bank_account['bank_name'] . ' [ ' . $bank_account['description'] . ' ]' . '</option>';
                        }
                        ?>    

                    </select>   

                </div>
            </div>




        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right">Load</button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
