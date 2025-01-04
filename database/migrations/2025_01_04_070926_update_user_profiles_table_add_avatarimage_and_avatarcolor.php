<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('avatarImage', 255)->nullable()->after('last_activity');
            $table->string('avatarColor', 7)->default('#ffffff')->after('avatarImage');
            $table->dropColumn('avatar');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('avatar', 255)->nullable()->after('last_activity');
            $table->dropColumn(['avatarImage', 'avatarColor']);
        });
    }
};
