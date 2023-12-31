<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string("title", 100);
            $table->text("description");
            $table->foreignId("user_id")->constrained("users")->onDelete("cascade");
            $table->integer("author_id")->nullable(true);
            $table->timestamp("publish_at")->nullable(true);
            $table->enum("privacy", ["public", "private"])->default("public");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
