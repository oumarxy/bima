<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div> 
            <div class="form-group">
                <label for="inputIssueddate" class="col-sm-4 control-label">Date Issued: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($issued_on); ?></div>
            </div>

            <div class="form-group">
                <label for="inputBankaccount" class="col-sm-4 control-label">Account: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="bank_account" class="form-control select2" style="width: 70%;">   
                        <option value="">Select Account</option>
                        <?php
                        foreach ($bank_account_list as $bank_account) {
                            $selected = (isset($id_bank_account) && $id_bank_account == $bank_account['id_bank_account']) ? 'selected="selected"' : '';
                            echo '<option value="' . $bank_account['id_bank_account'] . '" ' . $selected . '>' . $bank_account['bank_name'] . ' [ ' . $bank_account['description'] . ' ]' . '</option>';
                        }
                        ?>    

                    </select>   

                </div>
            </div> 

            <div class="form-group">
                <label for="inputChequenumber" class="col-sm-4 control-label">Cheque:<?php if (isset($id_transaction_type) && $id_transaction_type == 2): ?><span class="text-red">*</span><?php endif; ?> </label>
                <div class="col-sm-8"><?php echo form_input($cheque_number); ?></div>
            </div>  

            <div class="form-group">
                <label for="inputAmount" class="col-sm-4 control-label">Amount: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($amount); ?></div>
            </div>  

            <?php if (isset($id_transaction_type) && $id_transaction_type <> 1): ?>
                <div class="form-group">
                    <label for="inputPayee" class="col-sm-4 control-label">Particular: <span class="text-red">*</span></label>
                    <div class="col-sm-8"><?php echo form_input($particular); ?></div>
                </div>

            <?php endif; ?>
                <div class="form-group">
                    <label  class="col-sm-4 control-label">Description: <span class="text-red">*</span></label>
                    <div class="col-sm-8"><?php echo form_input($comment); ?></div>
                </div>
        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_bank_transaction) ? 'Save' : 'Add'; ?></button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
