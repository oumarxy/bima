<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div>

            <div class="form-group">
                <label for="inputDaterange" class="col-sm-4 control-label">Issued: </label>
                <div class="col-sm-8"><?php echo form_input($date_range); ?></div>
            </div>

            <div class="form-group">
                <label for="inputDaterange" class="col-sm-4 control-label">Expired: </label>
                <div class="col-sm-8"><?php echo form_input($expired_status); ?></div>
            </div>

                <div class="form-group">
                <label for="inputInsurer" class="col-sm-4 control-label"> Insurer: </label>
                <div class="col-sm-8">
                    <select name="insurer" class="form-control select2" style="width: 55%;">
                        <option value="">Select Insurer</option>
                        <?php
                        foreach ($insurer_list as $insurer_) {
                            $selected = (isset($insurer) && $insurer == $insurer_['id_insurer']) ? 'selected="selected"' : '';
                            echo '<option value="' . $insurer_['id_insurer'] . '"' . $selected . ' >' . $insurer_['name'] . '</option>';
                        }
                        ?>

                    </select>

                </div>
            </div>

            <div class="form-group">
                <label for="inputType" class="col-sm-4 control-label"> Type: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="type" class="form-control select2" style="width: 75%;">
                        <option value="">Select Type</option>
                        <option value="1" <?php echo (isset($type) && $type == 1) ? 'selected="selected"' : ''; ?>>Motor Insurance</option>
                        <option value="2" <?php echo (isset($type) && $type == 2) ? 'selected="selected"' : ''; ?>>Non Motor Insurance</option>
                    </select>

                </div>
            </div>

            <div class="form-group">
                <label for="inputRemark" class="col-sm-4 control-label"> Remark: </label>
                <div class="col-sm-8">
                    <select name="remark" class="form-control select2" style="width: 55%;">
                        <option value="">Select Remark</option>
                        <?php
                        echo '<option value="1" >Full Paid</option>';
                        echo '<option value="0" >Not Paid</option>';
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
