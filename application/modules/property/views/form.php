<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">
        <?php echo form_open_multipart(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div>
            <div class="form-group">
                <label  class="col-sm-4 control-label">Type: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="property_type" class="form-control select2" style="width: 54%;" <?php echo "onchange=\"showPropertyNumber(this.value,'$number')\"" ?>>
                        <option value="">Select Type</option>
                        <?php
                        foreach ($property_type_list as $property_type) {
                            $selected = (isset($id_property_type) && $id_property_type == $property_type['id_property_type']) ? 'selected="selected"' : '';
                            echo '<option value="' . $property_type['id_property_type'] . '" ' . $selected . '>' . $property_type['type'] . '</option>';
                        }
                        ?>

                    </select>

                </div>
            </div>

            <div class="form-group">
                <label  class="col-sm-4 control-label">Description: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($description); ?></div>
            </div>

            <div id="txtnumber">

            </div>


            <div class="form-group">
                <label for="inputPropertyvalue" class="col-sm-4 control-label">Value: </label>
                <div class="col-sm-8"><?php echo form_input($property_value); ?></div>
            </div>


            <div class="form-group">
                <label for="inputclaim" class="col-sm-4 control-label"> Claim: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="claim" class="form-control select2" style="width: 44%;">
                        <option value="">-------Select-------</option>
                        <?php
                        foreach ($status_claim as $key => $value) {
                            $selected = ( $claim == $key && $claim <> '') ? 'selected="selected"' : '';
                            echo '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                        }
                        ?>

                    </select>

                </div>
            </div>

            <div class="form-group">
                <label for="inputPhoto" class="col-sm-4 control-label">Photo:</label>

                <div class="col-sm-5">
                    <input type="file" name="userfile" accept="image/*">
                </div>
            </div>
        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_property) ? 'Save' : 'Register'; ?></button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
