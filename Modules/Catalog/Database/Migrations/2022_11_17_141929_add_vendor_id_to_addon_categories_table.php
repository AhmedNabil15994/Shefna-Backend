<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorIdToAddonCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addon_categories', function (Blueprint $table) {
            $table->bigInteger('vendor_id')->unsigned()->nullable()->after('sort');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('addon_categories', function (Blueprint $table) {
            $table->dropForeign('addon_categories_vendor_id_foreign');
            $table->dropIndex('addon_categories_vendor_id_foreign');
            $table->dropColumn(['vendor_id']);
        });
    }
}
