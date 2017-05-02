
<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php //echo $message;            ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Comm Date</th>
                        <th>Issued Name</th>
                        <?php if ($id_insurer == '') { ?>
                            <th>Insurer</th>
                        <?php } ?>
                        <th>Cover Note</th>
                        <th>Sticker Number</th>
                        <th>Property Number</th>
                        <th>Premium</th>
                        <th>Premium + Vat</th>
                        <th>Paid Amount</th>
                        <th>Expire</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($insurances as $insurance): ?>
                        <?php
                        if ($id_insurer == '') {
                            $insurer = Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                        }
                        $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                        $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();



                        $payment_query = Modules::run('payment/get_where_custom', array('id_insurance' => $insurance->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()));
                        $paid_amount = ($payment_query->num_rows() > 0) ? Modules::run('payment/sum_where', array('id_insurance' => $insurance->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain())) : 0;
                        $out_bal_amount = $insurance->premium + $insurance->vat - $paid_amount;
                        ?>
                        <tr>
                            <td><?php echo format_date('', $insurance->comm_on); ?></td>
                            <td><?php echo $customer->name; ?></td>
                            <?php if ($id_insurer == '') { ?>
                                <td><?php echo $insurer->name; ?></td>
                            <?php } ?>
                            <td><?php echo $insurance->cover_note; ?></td>
                            <td><?php echo $insurance->sticker_number; ?></td>
                            <td><?php echo $property->number; ?></td>
                            <td><?php echo number_format($insurance->premium); ?></td>
                            <td><?php echo number_format($insurance->premium + $insurance->vat); ?></td>
                            <td><?php echo ($paid_amount <> 0) ? number_format($paid_amount) : '-'; ?></td>
                            <td><?php echo format_date('', $insurance->expired_on); ?></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Comm Date</th>
                        <th>Issued Name</th>
                        <?php if ($id_insurer == '') { ?>
                            <th>Insurer</th>
                        <?php } ?>
                        <th>Cover Note</th>
                        <th>Sticker Number</th>
                        <th>Property Number</th>
                        <th>Premium</th>
                        <th>Premium + Vat</th>
                        <th>Paid Amount</th>
                        <th>Expire</th>
                    </tr>
                </tfoot>
            </table>
            <p>
                <?php if (count($insurances) > 0): ?>
                    <?php echo form_open('report/print_insurance_pdf', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $id_insurer; ?>" name="insurer"/>
                    <input type="hidden" value="<?php echo $expired_from_date; ?>" name="expired_from_date"/>
                    <input type="hidden" value="<?php echo $expired_to_date; ?>" name="expired_to_date"/>
                    <input type="hidden" value="<?php echo $type; ?>" name="type"/>
                    <input type="hidden" value="<?php echo $remark; ?>" name="remark"/>

                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="pdf" value="Export to PDF"/>
                    <?php echo form_close(); ?>

                    &nbsp; &nbsp; &nbsp;

                    <?php echo form_open('report/print_insurance_excel', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $id_insurer; ?>" name="insurer"/>
                    <input type="hidden" value="<?php echo $expired_from_date; ?>" name="expired_from_date"/>
                    <input type="hidden" value="<?php echo $expired_to_date; ?>" name="expired_to_date"/>
                    <input type="hidden" value="<?php echo $type; ?>" name="type"/>
                    <input type="hidden" value="<?php echo $remark; ?>" name="remark"/>
                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="excel" value="Export to Excel"/>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </p>
        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
