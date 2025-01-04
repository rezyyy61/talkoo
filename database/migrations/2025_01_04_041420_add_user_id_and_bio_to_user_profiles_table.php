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
            if (!Schema::hasColumn('user_profiles', 'userId')) {
                $table->string('userId')->unique()->after('user_id');
            }

            if (!Schema::hasColumn('user_profiles', 'bio')) {
                $table->text('bio')->nullable()->after('avatar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('user_profiles', 'userId')) {
                $table->dropColumn('userId');
            }

            if (Schema::hasColumn('user_profiles', 'bio')) {
                $table->dropColumn('bio');
            }
        });
    }
};
