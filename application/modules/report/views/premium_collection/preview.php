

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" >  <?php //echo $message;  ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Issued Name</th>
                        <th>Receipt</th>
                        <th>Cover Note</th>
                        <th>Amount</th>
                        <th>Commission</th>
                        <th>Amount to Insurer</th>
                        <?php if ($this->input->post('insurer') == '') { ?>
                            <th>Insurer</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>   
                        <?php
                        $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $payment->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                        $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                        $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();

                        $vat = $insurance->vat;
                        $commission = $insurance->commission;
                        ?>
                        <tr>
                            <td><?php echo format_date('', $payment->paid_on); ?></td>
                            <td><?php echo $customer->name; ?></td>
                            <td><?php echo $payment->receipt; ?></td>
                            <td><?php echo $insurance->cover_note; ?></td>
                            <td><?php echo number_format($payment->amount); ?></td>
                            <td><?php echo number_format($commission); ?></td>
                            <td><?php echo number_format($payment->amount - $commission); ?></td>
                            <?php if ($this->input->post('insurer') == '') { ?>
                            
                                <td><?php echo Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->name; ?></td>
                            <?php } ?>

                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Date</th>
                        <th>Issued Name</th>
                        <th>Receipt</th>
                        <th>Cover Note</th>
                        <th>Amount</th>
                        <th>Commission</th>
                        <th>Amount to Insurer</th>
                        <?php if ($this->input->post('insurer') == '') { ?>
                            <th>Insurer</th>
                        <?php } ?>
                    </tr>
                </tfoot>
            </table>
            <p>
                <?php if (count($payments) > 0): ?>
                    <?php echo form_open('report/print_premium_collection_pdf', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $id_insurer; ?>" name="insurer"/>
                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="pdf" value="Export to PDF"/>
                    <?php echo form_close(); ?>

                    &nbsp; &nbsp; &nbsp;

                    <?php echo form_open('report/print_premium_collection_excel', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $id_insurer; ?>" name="insurer"/>
                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="excel" value="Export to Excel"/>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>