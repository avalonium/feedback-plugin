<?php namespace Avalonium\Feedback\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateRequestsTable Migration
 */
class CreateRequestsTable extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::create('avalonium_feedback_requests', function(Blueprint $table) {
            // Base
            $table->id();
            $table->uuid();
            $table->string('status', 10);
            $table->string('number', 10)->nullable();
            $table->string('firstname', 50)->nullable();
            $table->string('lastname', 50)->nullable();
            $table->string('email', 50)->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('message')->nullable();
            // Metrics
            $table->string('referer')->nullable();
            $table->string('ip')->nullable();
            $table->json('amo')->nullable();
            $table->json('utm')->nullable();
            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('avalonium_feedback_requests');
    }
}
