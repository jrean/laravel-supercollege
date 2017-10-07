<?php
/**
 * This file is part of Jrean\SuperCollege package.
 *
 * (c) Jean Ragouin <go@askjong.com> <www.askjong.com>
 */
namespace Jrean\SuperCollege;

use SoapClient;
use LogicException;
use Exception;

class SuperCollege
{
    /**
     * SOAP client instance.
     *
     * @var \SoapClient
     */
    protected $client;

    /**
     * SuperCollege API default parameters.
     *
     * @var array
     */
    protected $params = [
        "returnf"       => "",
        "apikey"        => "",
        "siteurl"       => "",
        "siteid"        => "",
        "searchrange"   => "0",
        "userid"        => "0",
        "usertype"      => "0",
        "restricttype"  => "0",
        "needtype"      => "0",
        "parenttype"    => "0",
        "sex"           => "1",
        "citizen"       => "1",
        "age"           => "0",
        "dobmonth"      => "0",
        "dobday"        => "0",
        "dobyear"       => "0",
        "zipcode"       => "0",
        "gpa"           => "0",
        "satv"          => "0",
        "satm"          => "0",
        "satw"          => "0",
        "act"           => "0",
        "maxincome"     => "0",
        "classrank"     => "0",
        "gradyear"      => "0",
        "major"         => "",
        "career"        => "",
        "interest"      => "",
        "race"          => "",
        "religion"      => "",
        "disability"    => "",
        "state"         => "",
        "membership"    => "",
        "military"      => "",
        "athletics"     => "",
        "circumstance"  => "",
        "collegechoice" => "",
    ];

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->checkDefaultConfig();

        $this->client = $this->makeClient();

        $this->prepareParams();
    }

    /**
     * Check if mandatory config values are set in config file.
     *
     * @return void
     *
     * @throws \LogicException
     */
    protected function checkDefaultConfig()
    {
        if (is_null($this->getWsdl()) || empty($this->getWsdl())) {
            throw new LogicException("SuperCollege - Wsdl not set in config file");
        }

        if (is_null($this->getApiKey()) || empty($this->getApiKey())) {
            throw new LogicException("SuperCollege - Api key not set in config file");
        }

        if (is_null($this->getSiteUrl()) || empty($this->getSiteUrl())) {
            throw new LogicException("SuperCollege - Site URL not set in config file");
        }

        if (is_null($this->getSiteId()) || empty($this->getSiteId())) {
            throw new LogicException("SuperCollege - Site ID not set in config file");
        }

        if (is_null($this->getReturnFormat()) || empty($this->getReturnFormat())) {
            throw new LogicException("SuperCollege - Return format not set in config file");
        }
    }

    /**
     * Make new SOAP client instance.
     *
     * @return \SoapClient
     */
    protected function makeClient()
    {
        $options = [
            'trace'      => true,
            'exceptions' => true,
            'encoding'   => "UTF-8",
        ];

        return new SoapClient($this->getWsdl(), $options);
    }

    /**
     * Inject authentification values in params.
     *
     * @return void
     */
    protected function prepareParams()
    {
        $this->params['returnf'] = $this->getReturnFormat();
        $this->params['apikey']  = $this->getApiKey();
        $this->params['siteurl'] = $this->getSiteUrl();
        $this->params['siteid']  = $this->getSiteId();
    }

    /**
     * Get the API Key from the config file.
     *
     * @return string|null
     */
    protected function getApiKey()
    {
        return config('supercollege.api_key');
    }

    /**
     * Get the site URL from the config file.
     *
     * @return string|null
     */
    protected function getSiteUrl()
    {
        return config('supercollege.site_url');
    }

    /**
     * Get the site ID from the config file.
     *
     * @return string|null
     */
    protected function getSiteId()
    {
        return config('supercollege.site_id');
    }

    /**
     * Get the return format from the config file.
     *
     * @return string|null
     */
    protected function getReturnFormat()
    {
        return config('supercollege.api_return_format');
    }

    /**
     * Get the wsdl from the config file.
     *
     * @return string|null
     */
    protected function getWsdl()
    {
        return config('supercollege.wsdl');
    }

    /**
     * Get params.
     *
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    /**
     * Get scholarships.
     *
     * @param  string  $params
     * @return \Illuminate\Support\Collection
     *
     * @throws \Exception
     */
    public function getScholarships($params)
    {
        try {
            $response = call_user_func_array(array($this->client, "findmatches"), array_flatten($params));

            return collect($response->scholarships->award);
        } catch (Exception $e) {
            // @TODO implement better exception handling.
            throw new Exception($e->getMessage() . ' - SoapClient error on findmatches method');
        }
    }

    /**
     * Get scholarship details.
     *
     * @param  string  $uuid
     * @return \stdClass
     *
     * @throws \Exception
     */
    public function getScholarshipDetails($uuid)
    {
        $params = array_only($this->getParams(), ['returnf', 'apikey', 'siteurl', 'siteid', 'userid']);

        $params = array_add($params, 's_uuid', $uuid);

        try {
            $response = call_user_func_array(array($this->client, "getdetails"), array_flatten($params));

            return $response->award->details;
        } catch (Exception $e) {
            // @TODO implement better exception handling.
            throw new Exception($e->getMessage() . ' - SoapClient error on getdetails method');
        }
    }

    /**
     * Get scholarships by params.
     *
     * @param  array  $params
     * @return \Illuminate\Support\Collection
     */
    public function getByParams(array $params)
    {
        $params = array_merge($this->getParams(), $params);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by major.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByMajor(array $id)
    {
        $param['major'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by career.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByCareer(array $id)
    {
        $param['career'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by interest.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByInterest(array $id)
    {
        $param['interest'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by ethnicity.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByEthnicity(array $id)
    {
        $param['race'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by disability.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByDisability(array $id)
    {
        $param['disability'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by state.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByState(array $id)
    {
        $param['state'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by membership.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByMembership(array $id)
    {
        $param['membership'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by military affiliation.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByMilitary(array $id)
    {
        $param['military'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by sport.
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getBySport(array $id)
    {
        $param['athletics'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by special circumstance.
     *
     * @param  string  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByCircumstance($id)
    {
        $param['circumstance'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

    /**
     * Get scholarships by college choice (opeid).
     *
     * @param  array  $id
     * @return \Illuminate\Support\Collection
     */
    public function getByCollegeChoice(array $id)
    {
        $param['collegechoice'] = implode(',', $id);

        $params = array_merge($this->getParams(), $param);

        return $this->getScholarships($params);
    }

}
