<?php


namespace IanKok\SurfForecastApiClient\WaveBreak;


use function GuzzleHttp\Promise\all;
use GuzzleHttp\Promise\PromiseInterface;
use IanKok\SurfForecastApiClient\Client\AuthenticatedSurfForecastClient;
use IanKok\SurfForecastApiClient\Entities\Region;
use IanKok\SurfForecastApiClient\Entities\WaveBreak;
use IanKok\SurfForecastApiClient\Region\RegionMapper;
use Psr\Http\Message\ResponseInterface;

class WaveBreakRepositoryAdapter
{

    /**
     * @var WaveBreakRepository
     */
    protected $waveBreakRepository;

    /**
     * @var WaveBreakMapper
     */
    protected $waveBreakMapper;

    /**
     * @var RegionMapper
     */
    protected $regionMapper;

    /**
     * @var AuthenticatedSurfForecastClient
     */
    protected $client;

    /**
     * @var ResponseInterpreter
     */
    protected $interpreter;

    /**
     * WaveBreakRepositoryAdapter constructor.
     *
     * @param WaveBreakRepository             $waveBreakRepository
     * @param WaveBreakMapper                 $waveBreakMapper
     * @param RegionMapper                    $regionMapper
     * @param AuthenticatedSurfForecastClient $client
     * @param ResponseInterpreter             $interpreter
     */
    public function __construct(
        WaveBreakRepository $waveBreakRepository,
        WaveBreakMapper $waveBreakMapper,
        RegionMapper $regionMapper,
        AuthenticatedSurfForecastClient $client,
        ResponseInterpreter $interpreter
    ) {
        $this->waveBreakRepository = $waveBreakRepository;
        $this->waveBreakMapper     = $waveBreakMapper;
        $this->regionMapper        = $regionMapper;
        $this->client              = $client;
        $this->interpreter         = $interpreter;
    }

    /**
     * @param string $countyId
     *
     * @return array | WaveBreak[]
     */
    public function getByCountryId(string $countyId): array
    {
        return $this->getByCountryIdAsync($countyId)->wait();
    }

    /**
     * @param string $countyId
     *
     * @return PromiseInterface
     */
    public function getByCountryIdAsync(string $countyId): PromiseInterface
    {
        return $this->client->requestAsync(
            'GET',
            'http://www.surf-forecast.com/countries/' . $countyId . '/regions.js'
        )->then(
            function (ResponseInterface $response) {
                if ($this->interpreter->interpret($response) === 'region') {
                    $promises = array_map(
                        function (Region $region) {
                            return $this->waveBreakRepository->getByRegionIdAsync($region->getValue(), $region->getName());
                        },
                        $this->regionMapper->mapResponse($response)
                    );
                    all($promises)->wait();
                    return array_reduce(
                        array_map(
                            function ($promise) {
                                return $promise->wait();
                            },
                            $promises
                        ),
                        function ($carry, $item) {
                            return array_merge($carry, $item);
                        },
                        []
                    );
                }
                return $this->waveBreakMapper->mapResponse($response, 'Default Country Region');
            }
        );
    }
}