<?php
/**
 * Author : Danish Naseem <dani.115115@gmail.com>
 */

namespace FAAPI;

class Purchase
{
    public function post($rest)
    {
        include_once(API_ROOT . "/purchase.inc");
        purchase_add();
    }

    public function delete($rest, $branch_id, $uuid)
    {
        include_once(API_ROOT . "/purchase.inc");
        purchase_delete($branch_id, $uuid);
    }
}
