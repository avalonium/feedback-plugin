<?php namespace Avalonium\Feedback\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateLogsTable Migration
 */
class CreateLogsTable extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::create('avalonium_feedback_logs', function(Blueprint $table) {
            // Base
            $table->id();
            $table->string('type')->index();
            $table->text('message')->nullable();
            $table->text('details')->nullable();
            // Relation Backend.Models.User
            $table->unsignedInteger('backend_user_id')->nullable();
            $table->foreign('backend_user_id')->references('id')->on('backend_users')->nullOnDelete();
            // Relation Morphs
            $table->numericMorphs('loggable');
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('avalonium_feedback_logs');
    }
}
