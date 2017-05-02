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
                <label for="inputCoverNote" class="col-sm-4 control-label">Cover Note: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($cover_note); ?></div>
            </div>
            <div class="form-group">
                <label for="inputAmount" class="col-sm-4 control-label">Amount: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($amount); ?></div>
            </div>

            <div class="form-group">
                <label for="inputReceipt" class="col-sm-4 control-label">Receipt: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($receipt); ?></div>
            </div>


        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right" onclick="return confirm('Do you want to perform this operation?')"><?php echo isset($id_payment) ? 'Save' : 'Add'; ?></button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
