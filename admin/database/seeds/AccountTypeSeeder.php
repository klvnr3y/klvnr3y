<?php

use App\AccountPlan;
use App\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $AccountType1 = AccountType::create([
            'type' => 'Cancer Caregiver',
            'description' => 'Cancer Caregiver',
        ]);
        $AccountType1->account_plan()->create([
            'plan' => 'Cancer Caregiver',
            'description' => 'Cancer Caregiver $25',
            'amount' => '25'
        ]);

        $AccountType1->privacy()->create([
            'privacy_policy' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum pulvinar sem massa, ut facilisis nunc iaculis quis. Nam vel tempor nisi. Duis id lacus eget dolor molestie malesuada. Nunc eu arcu laoreet, condimentum lorem et, efficitur libero. Etiam ac lacus et lorem faucibus lobortis. Sed varius ante sit amet nisl ornare, quis rutrum nisl suscipit. Phasellus maximus quam consequat felis egestas, at faucibus tellus facilisis. Maecenas tempor laoreet pellentesque. Nullam sodales erat odio, sed vestibulum ante convallis eget. Proin vestibulum pulvinar condimentum.

            Curabitur id arcu finibus, pharetra metus ac, mollis urna. Fusce vel ligula tempus, pretium libero ut, imperdiet neque. Donec lacinia nibh quis leo maximus fringilla commodo sit amet ipsum. Sed semper accumsan enim, id sodales sapien porta a. Nam cursus sit amet odio et gravida. Nam dictum purus et elit sollicitudin, a iaculis massa euismod. Nulla quis ultricies justo. Morbi suscipit pellentesque sollicitudin. Maecenas orci eros, vehicula id dignissim at, viverra a velit. Vestibulum ac velit velit. Morbi vehicula ornare vehicula. Proin aliquam nunc ut lacinia sagittis. Fusce convallis rhoncus erat, a pharetra neque sodales commodo. Vestibulum ultrices orci diam, at rhoncus metus varius et. Suspendisse id ante non orci viverra euismod.'
        ]);

        $AccountType2 = AccountType::create([
            'type' => 'Cancer Care Professional',
            'description' => 'Cancer Care Professional',
        ]);
        $AccountType2->account_plan()->create([
            'plan' => 'Cancer Care Professional',
            'description' => 'Cancer Care Professional $75',
            'amount' => '75'
        ]);
        $AccountType2->privacy()->create([
            'privacy_policy' => 'Nulla tincidunt urna lacus, nec porttitor metus euismod ac. Pellentesque varius vestibulum est, eu egestas sapien mattis sed. Sed suscipit lacus justo. Integer et erat a turpis pretium finibus. Etiam convallis nisl eu ipsum condimentum vulputate. Etiam tincidunt ligula sagittis urna volutpat, nec finibus felis ultrices. Aenean porttitor turpis arcu, ut venenatis purus vestibulum ut. Vestibulum faucibus sodales velit id eleifend. Praesent congue nisi non ligula maximus imperdiet. Donec scelerisque varius tortor at tincidunt. Suspendisse consectetur leo magna, hendrerit pulvinar metus facilisis eu. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Integer in libero in tellus ultricies dignissim. Phasellus quam arcu, gravida eu enim vel, commodo aliquam lorem.

            Quisque porta elit at enim semper eleifend. Aliquam venenatis dignissim elit ac dapibus. Sed eros lacus, ullamcorper vel magna a, cursus viverra ligula. Proin sagittis erat a sem pharetra, vitae iaculis tortor congue. Integer fermentum arcu nec metus finibus mattis. Etiam vitae fringilla nisi. Duis sem sapien, accumsan non vestibulum sit amet, tempor sed elit. Sed commodo neque sed venenatis feugiat. Vivamus posuere vel dolor ut pulvinar.'
        ]);
    }
}