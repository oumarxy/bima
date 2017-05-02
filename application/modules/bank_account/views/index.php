

<div class="col-xs-11">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Bank</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Number</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bank_accounts as $bank_account): ?>          
                        <tr>
                            <td><?php echo htmlspecialchars($bank_account->bank_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($bank_account->account_name, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($bank_account->description, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($bank_account->account_number, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo anchor("bank_account/edit/" . $bank_account->id_bank_account, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?> | <?php echo anchor("bank_account/delete/" . $bank_account->id_bank_account, '<i class="fa fa-trash"></i>', array('title' => "Delete", 'onclick' => "return confirm('Do you want delete this record')")); ?></td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Bank</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Number</th>
                        <th>Options</th>
                    </tr>
                </tfoot>
            </table>
            <p><?php echo anchor('bank_account/add', '<i class="fa fa-plus-circle"></i> Add account') ?> </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>