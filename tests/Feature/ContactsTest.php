<?php

namespace Tests\Feature;

use App\Contact;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function a_list_of_contacts_can_be_fetch_for_authenticated_user()
    {
        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $anotherUser = factory(User::class)->create();

        $contact = factory(Contact::class)->create(['user_id' => $user->id]);
        $anotherContact = factory(Contact::class)->create(['user_id' => $anotherUser->id]);

        $response = $this->get('api/contacts?api_token=' . $user->api_token);

        $response->assertJsonCount(1)->assertJson([['id' => $contact->id]]);
    }


    /** @test */
    public function an_unauthenticated_user_should_redirect_to_login()
    {
        $response = $this->post('/api/contacts', array_merge($this->data(), ['api_token' => '']));
        $response->assertRedirect('/login');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function an_authenticated_user_can_add_contact()
    {
        $this->post('/api/contacts', $this->data());
        $contact = Contact::first();

        $this->assertEquals('test name', $contact->name);
        $this->assertEquals('test@gmail.com', $contact->email);
        $this->assertEquals('05-03-1998', $contact->birthday->format('m-d-Y'));
        $this->assertEquals('ABC String', $contact->company);
    }


    /** @test */
    public function field_is_required()
    {
        collect(['name', 'email', 'birthday', 'company'])->each(
            function ($field) {
                $response = $this->post('/api/contacts', array_merge($this->data(), [$field => '']));
                $response->assertSessionHasErrors($field);
                $this->assertCount(0, Contact::all());
            }
        );
    }



    /** @test */
    public function a_email_must_valid_email()
    {
        $response = $this->post('/api/contacts', array_merge($this->data(), ['email' => 'dgdfdgdrsgd']));
        $response->assertSessionHasErrors('email');
        $this->assertCount(0, Contact::all());
    }

    /** @test */
    public function birthday_store_properly()
    {
        $response = $this->post('/api/contacts', array_merge($this->data(), ['birthday' => '3 May, 1998']));

        $this->assertCount(1, Contact::get());
        $this->assertInstanceOf(Carbon::class, Contact::first()->birthday);
        $this->assertEquals('05-03-1998', Contact::first()->birthday->format('m-d-Y'));
    }

    /** @test */
    public function a_contact_can_be_retrive()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);

        $response = $this->get('/api/contacts/' . $contact->id . '?api_token=' . $this->user->api_token);
        $response->assertJsonFragment([
            'name' => $contact->name,
            'email' => $contact->email,
            'birthday' => $contact->birthday,
            'company' => $contact->company
        ]);
    }

    /** @test */
    public function only_contacts_user_can_be_retrivied()
    {
        $contact = factory(Contact::class)->create(['user_id' => $this->user->id]);
        $anotheruser = factory(User::class)->create();
        $response = $this->get('/api/contacts/' . $contact->id . '?api_token=' . $anotheruser->api_token);

        $response->assertStatus(403);
    }

    /** @test */
    public function contact_can_be_put()
    {
        $this->withoutExceptionHandling();
        $contact = factory(Contact::class)->create();

        $response = $this->put('/api/contacts/' . $contact->id, $this->data());

        $contact = $contact->fresh();

        $this->assertEquals('test name', $contact->name);
        $this->assertEquals('test@gmail.com', $contact->email);
        $this->assertEquals('05-03-1998', $contact->birthday->format('m-d-Y'));
        $this->assertEquals('ABC String', $contact->company);
    }

    /** @test */
    public function a_contact_can_be_deleted()
    {
        $this->withoutExceptionHandling();
        $contact = factory(Contact::class)->create();

        $response = $this->delete('/api/contacts/' . $contact->id, ['api_token' => $this->user->api_token]);

        $this->assertCount(0, Contact::all());
    }

    public function data()
    {
        return [
            'name' => 'test name',
            'email' => 'test@gmail.com',
            'birthday' => '05/03/1998',
            'company' => 'ABC String',
            'api_token' => $this->user->api_token
        ];
    }
}
